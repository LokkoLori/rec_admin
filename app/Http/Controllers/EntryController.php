<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competition;
use App\Models\CompetitionDay;
use App\Models\Game;
use App\Models\Entry;

class EntryController extends Controller
{

    public function create()
    {
        $competitions = Competition::with('competitionDay', 'game')->get();
        
        $userId = auth()->id();
        $userEntries = Entry::where('user_id', $userId)->get();

        return view('entries.create', compact('competitions', 'userEntries'));
    }

    public function store(Request $request)
    {
        $userId = auth()->id();

        foreach ($request->competitions as $competitionId => $entered) {

            $entry = Entry::where('user_id', $userId)->where('competition_id', $competitionId)->first();
            
            if ($entered) {
                if (!$entry){
                    Entry::updateOrCreate([
                        'user_id' => $userId,
                        'competition_id' => $competitionId,
                        'note' => $request->note,
                    ]);
                } else {
                    $entry->note = $request->note;
                    $entry->save();
                }
            } else {
                if ($entry && $entry->points == 0) {
                    $entry->delete();
                }
            }
        }
    
        return redirect()->back()->with('success', 'Sucessfull entry');
    }
}
