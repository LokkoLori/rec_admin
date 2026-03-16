<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OBS Game View - {{ $competition ? $competition->game->name : 'Waiting...' }}</title>
    <style>
        /* CSS Reset and absolute positioning */
        body, html {
            margin: 0;
            padding: 0;
            background-color: transparent;
            font-family: 'Courier New', Courier, monospace;
            color: white;
            text-shadow: 2px 2px 4px #000000;
        }

        .obs-container {
            width: 1920px;
            height: 1080px;
            position: absolute;
            top: 0;
            left: 0;
            background-size: 1920px 1080px;
            background-repeat: no-repeat;
            background-position: top left;
            overflow: hidden;
        }

        /* --- EVENT INFO (Top Center) --- */
        .event-info {
            position: absolute;
            top: 130px; /* Adjust based on the top header graphics */
            width: 100%;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        /* --- MATCH TYPE (Bottom Center) --- */
        .match-type {
            position: absolute;
            bottom: 55px; /* Fits inside the bottom dark bar */
            width: 100%;
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            text-transform: uppercase;
            color: #f0f0f0;
            letter-spacing: 3px;
        }

        /* --- PLAYER NESTS (Sides) --- */
        .player-nest {
            position: absolute;
            bottom: 50px; /* Adjust to place right below the player camera/game boxes */
            width: 380px; 
            height: 60px;
            line-height: 60px;
            font-size: 32px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
            box-sizing: border-box;
        }

        #nest-left {
            left: 0;
        }

        #nest-right {
            right: 0;
            flex-direction: row-reverse; /* Puts score on the left, name on the right */
        }

        .score {
            color: #ffcc00; /* Distinct color for scores */
            font-size: 46px;
        }

        .name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
        }

    </style>
</head>
<body>

@if($competition && $activeMatch)
    @php
        // Determine background image based on game name
        $bgImage = 'rec_tetris_2026_editable.png'; 
        if (str_starts_with($competition->game->name, 'Wizard')) {
            $bgImage = 'rec_wow_2026_editable.png'; // Update this to your actual WoW game background filename
        }

        // Format match type string
        $matchTypeStr = '';
        switch ($activeMatch->type) {
            case 'qfn': $matchTypeStr = 'Quarter-final'; break;
            case 'sfn': $matchTypeStr = 'Semi-final'; break;
            case 'brz': $matchTypeStr = 'Bronze Match'; break;
            case 'fnl': $matchTypeStr = 'Final'; break;
            default: $matchTypeStr = 'Match';
        }

        // Get players safely
        $p1 = $activeMatch->participations[0] ?? null;
        $p2 = $activeMatch->participations[1] ?? null;
    @endphp

    <div class="obs-container" style="background-image: url('{{ asset('images/' . $bgImage) }}');">
        
        <!-- EVENT INFO -->
        <div class="event-info">
            {{ $competition->competitionDay->name }} | {{ \Carbon\Carbon::parse($competition->competitionDay->date)->format('Y.m.d') }}
        </div>

        <!-- LEFT PLAYER -->
        <div id="nest-left" class="player-nest">
            <span class="name">{{ $p1 && $p1->gamer ? $p1->gamer->nickname : 'TBD' }}</span>
            <span class="score">{{ $p1 ? $p1->score : '0' }}</span>
        </div>

        <!-- RIGHT PLAYER -->
        <div id="nest-right" class="player-nest">
            <span class="name">{{ $p2 && $p2->gamer ? $p2->gamer->nickname : 'TBD' }}</span>
            <span class="score">{{ $p2 ? $p2->score : '0' }}</span>
        </div>

        <!-- MATCH TYPE -->
        <div class="match-type">
            {{ $matchTypeStr }}
        </div>

    </div>
@else
    <!-- Standby screen -->
    <div style="width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; background: #111; color: white; font-size: 50px; font-family: sans-serif;">
        WAITING FOR MATCH TO START...
    </div>
@endif

<script>
    // Developer tool for pixel hunting - strictly relative to the container
    const container = document.querySelector('.obs-container');
    
    if(container) {
        container.addEventListener('click', function(e) {
            const rect = container.getBoundingClientRect();
            const x = Math.round(e.clientX - rect.left);
            const y = Math.round(e.clientY - rect.top);
            
            console.log(`CSS -> left/right spacing; top: ${y}px;`);
            
            let dot = document.createElement('div');
            dot.style.position = 'absolute';
            dot.style.left = x + 'px';
            dot.style.top = y + 'px';
            dot.style.width = '6px';
            dot.style.height = '6px';
            dot.style.backgroundColor = '#00ff00';
            dot.style.borderRadius = '50%';
            dot.style.transform = 'translate(-50%, -50%)';
            dot.style.zIndex = 9999;
            container.appendChild(dot);
        });
    }

    // Auto-refresh for OBS
    // Comment this out while measuring pixels!
    setTimeout(function(){ window.location.reload(1); }, 5000);
</script>

</body>
</html>