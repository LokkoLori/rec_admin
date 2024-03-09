<script>
    setTimeout(function() {
        window.location.reload(true); 
    }, 10000);
</script>

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
    <div>
    <h3>{{ $compo->game->name }}</h3>
    <table style="margin-right: 20px;">
    @foreach($compo->game->game_stations as $gs)
    <tr>
        <td>{{$gs->name}}<td>
        
        @php
        $act_match = $gs->actual_match();
        @endphp

        @if (is_null($act_match))
            <td>idle</td><td> - </td>
        @else
            <td>{{$act_match->status}}</td>
            <td>|
            @foreach ($act_match->participations as $p)
            {{ $p->gamer->nickname}} |
            @endforeach
            </td>
        @endif
    </tr>
    @endforeach
    </table>
    </div>
@endforeach
</div>