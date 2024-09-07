<h1>
|
@foreach($awaiting_gamers as $awaiting_gamer)
{{$awaiting_gamer}} | 
@endforeach
</h1>

<div style="display: flex;">
@php
$compos = $actual_day->competitions;
@endphp

@foreach($compos as $compo)
    <div style="border-right: 1px solid black; padding: 5px;">
    <p>{{ $compo->game->name }}</p>
    <table style="margin-right: 20px;">
    @foreach($compo->game->game_stations as $gs)
    <tr>
        <td>
            <p style="margin-bottom: 10px;">    
            {{$gs->name}}</br>
            
            @php
            $act_match = $gs->actual_match();
            @endphp

            @if (is_null($act_match))
                {{ $gs->available == 0 ? "out of order" : "idle" }} ---
            @else
                {{$act_match->status}} : 
                |
                @foreach ($act_match->participations as $p)
                {{ $p->gamer->nickname}} |
                @endforeach
            @endif
            </p>
        </td>
    </tr>
    @endforeach
    </table>
    </div>
@endforeach
</div>