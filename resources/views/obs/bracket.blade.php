<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OBS Bracket - {{ $competition ? $competition->game->name : 'Waiting...' }}</title>
    <style>
        /* CSS Reset and absolute positioning to prevent scaling issues */
        body, html {
            margin: 0;
            padding: 0;
            background-color: #222;
            font-family: 'Courier New', Courier, monospace;
            color: black;
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

        .player-box {
            position: absolute;
            width: 200px;
            height: 50px;
            line-height: 50px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
            box-sizing: border-box;
            background-color: white; 
            border: 2px solid transparent;
        }

        .active-match {
            box-shadow: 0 0 15px 5px red;
            background-color: rgba(255, 200, 200, 0.9); 
        }

        .score {
            color: darkred;
        }

        /* --- POSITIONS --- */
        
        /* Quarter-Finals Left */
        #qf1-p1 { top: 145px; left: 284px; } /* Set to your measured exact position */
        #qf1-p2 { top: 376px; left: 284px; }
        #qf4-p1 { top: 682px; left: 284px; }
        #qf4-p2 { top: 904px; left: 284px; }

        /* Semi-Finals Left */
        #sf1-p1 { top: 260px; left: 538px; }
        #sf1-p2 { top: 804px; left: 538px; }

        /* Quarter-Finals Right */
        #qf2-p1 { top: 145px; left: 1420px; }
        #qf2-p2 { top: 376px; left: 1420px; }
        #qf3-p1 { top: 683px; left: 1420px; }
        #qf3-p2 { top: 904px; left: 1420px; }

        /* Semi-Finals Right */
        #sf2-p1 { top: 260px; left: 1176px; }
        #sf2-p2 { top: 804px; left: 1176px; }

        /* Center Matches */
        #final-p1 { top: 519px; left: 695px; }
        #final-p2 { top: 519px; left: 1004px; }
        #bronze-p1 { top: 661px; left: 692px; }
        #bronze-p2 { top: 661px; left: 1004px; }

        /* Podium */
        #podium-1st { top: 105px; left: 786px; width: 331px; height: 84px; line-height: 84px; font-size: 40px; justify-content: center; background: transparent; border: none; }
        #podium-2nd { top: 226px; left: 851px; justify-content: center; background: transparent; border: none; }
        #podium-3rd { top: 303px; left: 851px; justify-content: center; background: transparent; border: none; }

    </style>
</head>
<body>

@if($competition)
    @php
        // Determine background image based on game name
        $bgImage = 'rec_tetris_tabella_top8_2026.png'; // default fallback
        if (str_starts_with($competition->game->name, 'Wizard')) {
            $bgImage = 'rec_wow_tabella_top8_2026.png';
        }
    @endphp

    <div class="obs-container" style="background-image: url('{{ asset('images/' . $bgImage) }}');">
        
        @php
            function renderBox($match, $playerIndex, $boxId, $isActive, $isRightSide = false) {
                $class = 'player-box' . ($isActive ? ' active-match' : '');
                
                if (!$match || !isset($match->participations[$playerIndex])) {
                    // Empty state formatting
                    $content = $isRightSide 
                        ? "<span class='score'></span><span class='name'>TBD</span>" 
                        : "<span class='name'>TBD</span><span class='score'></span>";
                    
                    return "<div id='{$boxId}' class='{$class}'>{$content}</div>";
                }
                
                $part = $match->participations[$playerIndex];
                $name = $part->gamer ? $part->gamer->nickname : 'TBD';
                $score = $part->score;
                
                // Swap score and name order for right side
                if ($isRightSide) {
                    $content = "<span class='score'>{$score}</span><span class='name'>{$name}</span>";
                } else {
                    $content = "<span class='name'>{$name}</span><span class='score'>{$score}</span>";
                }
                
                return "<div id='{$boxId}' class='{$class}'>{$content}</div>";
            }
        @endphp

        <!-- LEFT SIDE (Group 1) -->
        {!! renderBox($matches[0] ?? null, 0, 'qf1-p1', $activeMatchIndex === 0) !!}
        {!! renderBox($matches[0] ?? null, 1, 'qf1-p2', $activeMatchIndex === 0) !!}
        
        {!! renderBox($matches[4] ?? null, 0, 'sf1-p1', $activeMatchIndex === 4) !!}
        {!! renderBox($matches[4] ?? null, 1, 'sf1-p2', $activeMatchIndex === 4) !!}

        {!! renderBox($matches[3] ?? null, 0, 'qf4-p1', $activeMatchIndex === 3) !!}
        {!! renderBox($matches[3] ?? null, 1, 'qf4-p2', $activeMatchIndex === 3) !!}

        <!-- RIGHT SIDE (Group 2) -->
        {!! renderBox($matches[1] ?? null, 0, 'qf2-p1', $activeMatchIndex === 1, true) !!}
        {!! renderBox($matches[1] ?? null, 1, 'qf2-p2', $activeMatchIndex === 1, true) !!}
        
        {!! renderBox($matches[5] ?? null, 0, 'sf2-p1', $activeMatchIndex === 5, true) !!}
        {!! renderBox($matches[5] ?? null, 1, 'sf2-p2', $activeMatchIndex === 5, true) !!}

        {!! renderBox($matches[2] ?? null, 0, 'qf3-p1', $activeMatchIndex === 2, true) !!}
        {!! renderBox($matches[2] ?? null, 1, 'qf3-p2', $activeMatchIndex === 2, true) !!}

        <!-- CENTER (Finals) -->
        {!! renderBox($matches[7] ?? null, 0, 'final-p1', $activeMatchIndex === 7) !!}
        {!! renderBox($matches[7] ?? null, 1, 'final-p2', $activeMatchIndex === 7, true) !!}

        {!! renderBox($matches[6] ?? null, 0, 'bronze-p1', $activeMatchIndex === 6) !!}
        {!! renderBox($matches[6] ?? null, 1, 'bronze-p2', $activeMatchIndex === 6, true) !!}

        <!-- PODIUM -->
        <div id="podium-1st" class="player-box">
            {{ $podium['first'] ? $podium['first']->nickname : 'TBD' }}
        </div>
        <div id="podium-2nd" class="player-box">
            {{ $podium['second'] ? $podium['second']->nickname : 'TBD' }}
        </div>
        <div id="podium-3rd" class="player-box">
            {{ $podium['third'] ? $podium['third']->nickname : 'TBD' }}
        </div>

    </div>
@else
    <div style="width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; background: black; color: white; font-size: 50px; font-family: sans-serif;">
        WAITING FOR THE NEXT COMPETITION TO START...
    </div>
@endif

<script>
    // Developer tool for pixel hunting - strictly relative to the container
    const container = document.querySelector('.obs-container');
    
    if(container) {
        container.addEventListener('click', function(e) {
            const rect = container.getBoundingClientRect();
            
            // Calculate coordinates exactly inside the 1920x1080 box
            const x = Math.round(e.clientX - rect.left);
            const y = Math.round(e.clientY - rect.top);
            
            console.log(`CSS -> left: ${x}px; top: ${y}px;`);
            
            // Visual indicator
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

    // Comment this out while measuring!
    setTimeout(function(){ window.location.reload(1); }, 5000);
</script>

</body>
</html>