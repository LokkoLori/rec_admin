<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CompetitionDay;
use App\Models\Competition;
use App\Models\Gamer;
use App\Models\GameMatch;
use App\Models\GameMatchParticipation;
use Carbon\Carbon;

class ImportHistoricalMatches extends Command
{
    protected $signature = 'rec:import-historical {file} {--dry-run : Run without making modifications (validation only)}';
    protected $description = 'Validate and import historical matches from a CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        // Fetch all gamers and map to lowercase for exact matching
        $gamersCollection = Gamer::all();
        $gamerNames = $gamersCollection->pluck('nickname')->map(function ($name) {
            return mb_strtolower(trim($name));
        })->toArray();

        // Load all competition days to memory for date-based lookup
        $compDays = CompetitionDay::all();

        $validMatchTypes = ['qlf', 'qfn', 'sfn', 'brz', 'fnl'];
        $errors = [];
        $validRows = [];
        $rowNum = 1; // Start at 1 to account for the header

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Skip header

        $this->info("Starting validation" . ($isDryRun ? " (DRY RUN mode)" : "") . "...");

        while (($row = fgetcsv($file)) !== false) {
            $rowNum++;
            
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            if (count($row) < 8) {
                $errors[] = "Row {$rowNum}: Missing columns. Found " . count($row) . " columns.";
                continue;
            }

            $dateStr = trim($row[1]);
            $gameStr = mb_strtolower(trim($row[2]));
            $type = mb_strtolower(trim($row[3]));
            $p1Name = trim($row[4]);
            $p1Score = trim($row[5]);
            $p2Name = trim($row[6]);
            $p2Score = trim($row[7]);
            $ytLink = isset($row[8]) ? trim($row[8]) : '';
            $timestamp = isset($row[9]) ? trim($row[9]) : '';

            // --- 1. Validate Date and Competition Day ---
            try {
                $cleanDate = rtrim(str_replace('.', '-', $dateStr), '-');
                $dateFormatted = Carbon::parse($cleanDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Invalid date format: '{$dateStr}'.";
                continue; // Cannot proceed without a valid date
            }

            $compDay = $compDays->first(function($cd) use ($dateFormatted) {
                return Carbon::parse($cd->date)->format('Y-m-d') === $dateFormatted;
            });

            if (!$compDay) {
                $errors[] = "Row {$rowNum}: Competition Day not found for date: '{$dateFormatted}' ({$dateStr})";
            }

            // --- 2. Map Game ID ---
            $gameId = null;
            if ($gameStr === 'wow') {
                $gameId = 1;
            } elseif ($gameStr === 'tetris') {
                $gameId = 2;
            } else {
                $errors[] = "Row {$rowNum}: Unknown game: '{$gameStr}'. Allowed values: 'wow' or 'tetris'.";
            }

            // --- 3. Validate Match Type ---
            if (!in_array($type, $validMatchTypes)) {
                $errors[] = "Row {$rowNum}: Invalid match type '{$type}'. Allowed: " . implode(', ', $validMatchTypes);
            }

            // --- 4. Validate Scores ---
            if (!is_numeric($p1Score) || !is_numeric($p2Score)) {
                $errors[] = "Row {$rowNum}: Scores must be numeric. Got P1: '{$p1Score}', P2: '{$p2Score}'";
            }

            // --- 5. Validate Gamers (Exact match only) ---
            if (mb_strtolower($p1Name) !== 'bye' && !in_array(mb_strtolower($p1Name), $gamerNames)) {
                $errors[] = "Row {$rowNum}: Gamer 1 not found in database: '{$p1Name}'";
            }
            if (mb_strtolower($p2Name) !== 'bye' && !in_array(mb_strtolower($p2Name), $gamerNames)) {
                $errors[] = "Row {$rowNum}: Gamer 2 not found in database: '{$p2Name}'";
            }

            // Store valid row data for potential import
            if (empty($errors)) {
                $validRows[] = [
                    'compDayId' => $compDay ? $compDay->id : null,
                    'gameId' => $gameId,
                    'type' => $type,
                    'p1Name' => $p1Name,
                    'p1Score' => $p1Score,
                    'p2Name' => $p2Name,
                    'p2Score' => $p2Score,
                    'ytLink' => $ytLink,
                    'timestamp' => $timestamp
                ];
            }
        }

        fclose($file);

        // --- VALIDATION RESULTS ---
        if (count($errors) > 0) {
            $this->error("Validation failed! Found " . count($errors) . " errors:");
            foreach ($errors as $error) {
                $this->line("- " . $error);
            }
            $this->warn("Fix the CSV and try again. No data was inserted.");
            return;
        }

        $this->info("Validation successful! All " . count($validRows) . " match records are valid.");

        if ($isDryRun) {
            $this->line("Executed in DRY RUN mode. Stopping here, no database changes were made.");
            return;
        }

        $this->info("Importing matches...");
        $this->executeImport($validRows, $gamersCollection);
    }

    private function executeImport(array $rows, $gamersCollection)
    {
        DB::beginTransaction();
        try {
            $bar = $this->output->createProgressBar(count($rows));
            $bar->start();

            foreach ($rows as $row) {
                // Find or create the Competition for the given day and game
                $competition = Competition::firstOrCreate([
                    'competition_day_id' => $row['compDayId'],
                    'game_id' => $row['gameId'],
                ], [
                    'final_status' => 'finished'
                ]);

                // Assemble note link with timestamp
                $note = $row['ytLink'];
                if (!empty($row['ytLink']) && $row['timestamp'] !== '') {
                    $separator = str_contains($row['ytLink'], '?') ? '&' : '?';
                    $note = $row['ytLink'] . $separator . 't=' . $row['timestamp'] . 's';
                }

                // Determine the game station ID based on the game ID (1 = WoW -> Station 1, 2 = Tetris -> Station 5)
                $gameStationId = ($row['gameId'] === 1) ? 1 : 5;

                // Create the match record
                $match = GameMatch::create([
                    'competition_id' => $competition->id,
                    'type' => $row['type'],
                    'status' => 'finished',
                    'note' => $note,
                    'game_station_id' => $gameStationId
                ]);

                // Record Player 1 participation
                if (mb_strtolower($row['p1Name']) !== 'bye') {
                    $gamer1 = $gamersCollection->first(fn($g) => mb_strtolower($g->nickname) === mb_strtolower($row['p1Name']));
                    GameMatchParticipation::create([
                        'game_match_id' => $match->id,
                        'gamer_id' => $gamer1->id,
                        'score' => (int) $row['p1Score']
                    ]);
                }

                // Record Player 2 participation
                if (mb_strtolower($row['p2Name']) !== 'bye') {
                    $gamer2 = $gamersCollection->first(fn($g) => mb_strtolower($g->nickname) === mb_strtolower($row['p2Name']));
                    GameMatchParticipation::create([
                        'game_match_id' => $match->id,
                        'gamer_id' => $gamer2->id,
                        'score' => (int) $row['p2Score']
                    ]);
                }

                $bar->advance();
            }

            $bar->finish();
            DB::commit();
            $this->newLine(2);
            $this->info("Import completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error("Database error during import: " . $e->getMessage());
        }
    }
}