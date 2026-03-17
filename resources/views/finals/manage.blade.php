<div style="margin: 20px; font-family: sans-serif;">
    <h2>Manage Active Match: {{ $competition->game->name }}</h2>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('finals.index') }}" style="text-decoration: none; color: #007bff;">&larr; Back to Competitions</a>
    </div>

    @if(session('error'))
        <div style="background-color: darkred; color: white; padding: 10px; margin-bottom: 20px;">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div style="background-color: darkgreen; color: white; padding: 10px; margin-bottom: 20px;">
            <strong>Success:</strong> {{ session('success') }}
        </div>
    @endif

    @if($activeMatch)
        <div style="border: 1px solid #ccc; padding: 20px; max-width: 600px; background-color: #f9f9f9;">
            <h3 style="margin-top: 0; text-transform: uppercase; text-align: center; color: #333;">
                @switch($activeMatch->type)
                    @case('qfn') Quarter-Final @break
                    @case('sfn') Semi-Final @break
                    @case('brz') Bronze Match @break
                    @case('fnl') Final @break
                    @default {{ $activeMatch->type }}
                @endswitch
                <span style="font-size: 14px; color: #777; display: block; margin-top: 5px;">Match ID: #{{ $activeMatch->id }}</span>
            </h3>

            <div style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0;">
                
                @php $p1 = $activeMatch->participations[0] ?? null; @endphp
                @if($p1)
                    <div style="text-align: center; background: white; padding: 20px; border: 2px solid #ddd; border-radius: 8px; width: 40%;">
                        <h4 style="margin: 0 0 20px 0; font-size: 22px;">{{ $p1->gamer ? $p1->gamer->nickname : 'BYE' }}</h4>
                        <div style="display: flex; justify-content: center; align-items: center; gap: 15px;">
                            <form action="{{ route('finals.update_score', $p1->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" style="background: #dc3545; color: white; border: none; width: 40px; height: 40px; font-size: 24px; cursor: pointer; border-radius: 5px;">-</button>
                            </form>
                            <span style="font-size: 36px; font-weight: bold; width: 50px;">{{ $p1->score }}</span>
                            <form action="{{ route('finals.update_score', $p1->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" style="background: #28a745; color: white; border: none; width: 40px; height: 40px; font-size: 24px; cursor: pointer; border-radius: 5px;">+</button>
                            </form>
                        </div>
                    </div>
                @endif

                <div style="font-size: 28px; font-weight: bold; color: #999;">VS</div>

                @php $p2 = $activeMatch->participations[1] ?? null; @endphp
                @if($p2)
                    <div style="text-align: center; background: white; padding: 20px; border: 2px solid #ddd; border-radius: 8px; width: 40%;">
                        <h4 style="margin: 0 0 20px 0; font-size: 22px;">{{ $p2->gamer ? $p2->gamer->nickname : 'BYE' }}</h4>
                        <div style="display: flex; justify-content: center; align-items: center; gap: 15px;">
                            <form action="{{ route('finals.update_score', $p2->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" style="background: #dc3545; color: white; border: none; width: 40px; height: 40px; font-size: 24px; cursor: pointer; border-radius: 5px;">-</button>
                            </form>
                            <span style="font-size: 36px; font-weight: bold; width: 50px;">{{ $p2->score }}</span>
                            <form action="{{ route('finals.update_score', $p2->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" style="background: #28a745; color: white; border: none; width: 40px; height: 40px; font-size: 24px; cursor: pointer; border-radius: 5px;">+</button>
                            </form>
                        </div>
                    </div>
                @elseif($p1 && !$p2)
                    <div style="text-align: center; background: #eee; padding: 20px; border: 2px dashed #ccc; border-radius: 8px; width: 40%; color: #777;">
                        <h4 style="margin: 0 0 10px 0; font-size: 20px;">NO OPPONENT</h4>
                        <div style="font-size: 14px;">Automatic advance to next round.</div>
                    </div>
                @endif
            </div>

            <div style="text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
                <form action="{{ route('finals.finish_match', $activeMatch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to finish this match?');">
                    @csrf
                    <button type="submit" style="background: darkorange; color: white; border: none; padding: 15px 40px; font-size: 20px; font-weight: bold; cursor: pointer; border-radius: 5px; width: 100%;">
                        Finish Match & Load Next
                    </button>
                </form>
            </div>
        </div>
    @else
        <div style="border: 1px solid #ccc; padding: 30px; max-width: 600px; background-color: #e8f5e9; text-align: center; border-radius: 8px;">
            <h3 style="color: darkgreen; font-size: 24px; margin-top: 0;">Tournament Complete!</h3>
            <p style="font-size: 16px;">All matches for {{ $competition->game->name }} have been finished.</p>
        </div>
    @endif
</div>