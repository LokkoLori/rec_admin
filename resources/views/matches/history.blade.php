<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REC Match History</title>
    <style>
        /* Retro CSS Reset and Font */
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: 'Courier New', Courier, monospace; /* Retro fallback */
            color: #000;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* --- HEADER --- */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-logo {
            max-width: 280px;
            display: inline-block;
        }

        /* --- FILTER FORM (Retro block style) --- */
        .filter-form {
            background: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
        }

        .filter-form form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-form label {
            display: block;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .filter-form select {
            width: 100%;
            padding: 8px;
            border: 2px solid #000;
            background: #fff;
            font-family: inherit;
            font-weight: bold;
            outline: none;
        }

        .filter-form select:focus {
            background: #ffe;
        }

        .filter-buttons {
            flex: 0 0 auto;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-retro {
            background: #ed1c24; /* REC Red */
            color: white;
            border: 2px solid #000;
            padding: 8px 20px;
            font-family: inherit;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 2px 2px 0px #000;
            transition: all 0.1s;
        }

        .btn-retro:active {
            box-shadow: 0px 0px 0px #000;
            transform: translate(2px, 2px);
        }

        .clear-link {
            color: #000;
            font-weight: bold;
            text-decoration: none;
            text-transform: uppercase;
            border-bottom: 2px solid transparent;
        }

        .clear-link:hover {
            border-bottom: 2px solid #ed1c24;
        }

        /* --- MATCH CARDS (Responsive) --- */
        .match-card {
            background: #fff;
            border: 2px solid #000;
            margin-bottom: 20px;
            box-shadow: 4px 4px 0px #000;
            display: flex;
            flex-direction: column;
        }

        /* Desktop layout for cards */
        @media (min-width: 768px) {
            .match-card {
                flex-direction: row;
            }
        }

        /* Main Result Block (Left side) */
        .match-result {
            flex: 1;
            background: #f8f9fa;
            border-bottom: 2px solid #000;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        @media (min-width: 768px) {
            .match-result {
                border-bottom: none;
                border-right: 2px solid #000;
                max-width: 350px;
            }
        }

        .score-display {
            font-size: 42px;
            font-weight: bold;
            color: #ed1c24;
            letter-spacing: 2px;
            text-shadow: 1px 1px 0px #000;
        }

        .player-display {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            margin-bottom: 10px;
        }

        .player-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .player-name.left { text-align: right; }
        .player-name.right { text-align: left; }

        .vs-badge {
            font-size: 14px;
            color: #777;
        }

        /* Details Block (Right side) */
        .match-details {
            flex: 2;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            align-content: center;
        }

        @media (min-width: 992px) {
            .match-details {
                grid-template-columns: 1fr 1fr;
            }
        }

        .detail-item {
            font-size: 15px;
        }

        .detail-item strong {
            display: inline-block;
            width: 120px;
            color: #555;
            text-transform: uppercase;
            font-size: 13px;
        }

        .badge {
            background: #000;
            color: #fff;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-finished { color: #ed1c24; font-weight: bold; }
        .status-started { color: #28a745; font-weight: bold; }
        .status-pending { color: #777; font-weight: bold; }

        /* --- PAGINATION FIX --- */
        .pagination-container {
            margin-top: 30px;
            background: #fff;
            padding: 15px;
            border: 2px solid #000;
            box-shadow: 4px 4px 0px #000;
        }

        /* Fix the giant SVG arrows caused by missing Tailwind CSS */
        .pagination-container svg {
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }
        .w-5 { width: 1.25rem; }
        .h-5 { height: 1.25rem; }

        /* Style the Laravel default pagination slightly better */
        .pagination-container nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .pagination-container a {
            color: #ed1c24;
            font-weight: bold;
        }

    </style>
</head>
<body>

<div class="container">
    
    <div class="header">
        <img src="{{ asset('images/reclogo.png') }}" alt="REC Logo" class="header-logo">
    </div>

    <div class="filter-form">
        <form action="{{ route('matches.history') }}" method="GET">
            
            <div class="filter-group">
                <label>Season</label>
                <select name="season_id">
                    <option value="">-- All Seasons --</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>
                            {{ $season->name ?? $season->year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Day</label>
                <select name="competition_day_id">
                    <option value="">-- All Days --</option>
                    @foreach($days as $day)
                        <option value="{{ $day->id }}" {{ request('competition_day_id') == $day->id ? 'selected' : '' }}>
                            {{ $day->name }} ({{ \Carbon\Carbon::parse($day->date)->format('Y-m-d') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Game</label>
                <select name="game_id">
                    <option value="">-- All Games --</option>
                    @foreach($games as $game)
                        <option value="{{ $game->id }}" {{ request('game_id') == $game->id ? 'selected' : '' }}>
                            {{ $game->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Player</label>
                <select name="gamer_id">
                    <option value="">-- All Players --</option>
                    @foreach($gamers as $gamer)
                        <option value="{{ $gamer->id }}" {{ request('gamer_id') == $gamer->id ? 'selected' : '' }}>
                            {{ $gamer->nickname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn-retro">Filter</button>
                <a href="{{ route('matches.history') }}" class="clear-link">Clear</a>
            </div>
        </form>
    </div>

    <div class="match-list">
        @forelse($matches as $match)
            @php
                $compo = $match->competition;
                $day = $compo ? $compo->competitionDay : null;
                $season = $day ? $day->season : null;
                
                $matchType = $match->type;
                $typeLabels = [
                    'qlf' => 'Qualifier',
                    'qfn' => 'Quarter-Final',
                    'sfn' => 'Semi-Final',
                    'brz' => 'Bronze Match',
                    'fnl' => 'Final'
                ];
                $displayType = $typeLabels[$matchType] ?? strtoupper($matchType);

                $p1 = $match->participations[0] ?? null;
                $p2 = $match->participations[1] ?? null;
            @endphp
            
            <div class="match-card">
                <div class="match-result">
                    <div class="player-display">
                        <span class="player-name left" title="{{ $p1 && $p1->gamer ? $p1->gamer->nickname : 'BYE' }}">
                            {{ $p1 && $p1->gamer ? $p1->gamer->nickname : 'BYE' }}
                        </span>
                        <span class="vs-badge">VS</span>
                        <span class="player-name right" title="{{ $p2 && $p2->gamer ? $p2->gamer->nickname : 'BYE' }}">
                            {{ $p2 && $p2->gamer ? $p2->gamer->nickname : 'BYE' }}
                        </span>
                    </div>
                    <div class="score-display">
                        {{ $p1 ? $p1->score : '0' }} - {{ $p2 ? $p2->score : '0' }}
                    </div>
                </div>

                <div class="match-details">
                    <div class="detail-item">
                        <strong>Game:</strong> {{ $compo && $compo->game ? $compo->game->name : 'N/A' }}
                    </div>
                    <div class="detail-item">
                        <strong>Match Type:</strong> <span class="badge">{{ $displayType }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Event Day:</strong> {{ $day ? \Carbon\Carbon::parse($day->date)->format('Y-m-d') . ' - ' . $day->name : 'N/A' }}
                    </div>
                    <div class="detail-item">
                        <strong>Status:</strong>
                        @if($match->status === 'finished')
                            <span class="status-finished">FINISHED</span>
                        @elseif($match->status === 'started')
                            <span class="status-started">STARTED</span>
                        @else
                            <span class="status-pending">PENDING</span>
                        @endif
                    </div>
                    <div class="detail-item" style="color: #999; font-size: 12px;">
                        <strong>Match ID:</strong> #{{ $match->id }}
                    </div>
                    @if($match->note)
                        <div class="match-note" style="border-top: 1px dashed #ccc; padding: 10px 20px; background: #fafafa; font-size: 14px; color: #333;">
                            <strong>Note:</strong> 
                            {!! preg_replace(
                                '/(https?:\/\/[^\s]+)/', 
                                '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: #007bff; text-decoration: none; font-weight: bold;">$1</a>', 
                                e($match->note)
                            ) !!}
                        </div>
                    @endif
                </div>
            </div>
            
        @empty
            <div style="background: #fff; border: 2px solid #000; padding: 30px; text-align: center; font-weight: bold; box-shadow: 4px 4px 0px #000;">
                No matches found matching these criteria.
            </div>
        @endforelse
    </div>

    @if($matches->hasPages())
        <div class="pagination-container">
            {{ $matches->withQueryString()->links() }}
        </div>
    @endif

</div>

</body>
</html>