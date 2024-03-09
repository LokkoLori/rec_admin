<x-app>
{{$actual_day->season->year}} - {{$actual_day->name}}
<table>
@foreach ($game_stations as $game_station)
<tr>
    <td>
        {{ $game_station->name }}
    </td>
    <td>
    @php 
        $act_match = $game_station->actual_match();

        if (!function_exists('mm_gamer_name')) {
            function mm_gamer_name($gamer, $act_compo){
                return $gamer->nickname."(".$gamer->finished_participations($act_compo)->count().")";
            }
        }

    @endphp

    @if (is_null($act_match))
        <form action="{{ route('matchmaking.create_match') }}" method="POST">
            <input type="hidden" name="game_station_id" value="{{ $game_station->id }}"/> 
            <button type="submit" class="btn btn-success">Create Match</button>
            @csrf
        </form>
    @else
        @php
            $act_compo = $act_match->competition;
            $gamer_1 = $act_match->participations->get(0)->gamer;
            $gamer_2 = $act_match->participations->get(1)->gamer;
        @endphp

        @if ($act_match->status == 'waiting')
            <form action="{{ route('matchmaking.start_match') }}" method="POST">
            {{ $act_compo->game->name }} : 
            {{ mm_gamer_name($gamer_1, $act_compo) }}
                <select name="sub_gamer_1_id" id="sub_gamer_1_id" class="form-control">
                    <option value="0">-</option>
                    @foreach ($free_gamers_tables[$act_compo->id] as $free_gamer)
                        <option value="{{$free_gamer['gamer']->id}}">{{$free_gamer['gamer']->nickname}}({{$free_gamer['match_count']}})</option>
                    @endforeach
                </select>
            vs 
            {{ mm_gamer_name($gamer_2, $act_compo) }}
                <select name="sub_gamer_2_id" id="sub_gamer_2_id" class="form-control">
                    <option value="0">-</option>
                    @foreach ($free_gamers_tables[$act_compo->id] as $free_gamer)
                        <option value="{{$free_gamer['gamer']->id}}">{{$free_gamer['gamer']->nickname}}({{$free_gamer['match_count']}})</option>
                    @endforeach
                </select>

                <input type="hidden" name="game_match_id" value="{{ $act_match->id }}"/> 
                <button type="submit" name="match_action" value="start" class="btn btn-success">Start</button>
                <button type="submit" name="match_action" value="update" class="btn btn-success">Update</button>
                <button type="submit" name="match_action" value="cancel" class="btn btn-success">Cancel</button>
            @csrf
        </form>
        @endif
        @if ($act_match->status == 'started')
            <form action="{{ route('matchmaking.finish_match') }}" method="POST">
                {{ $act_compo->game->name }} :
                <input type="hidden" name="game_match_id" value="{{ $act_match->id }}"/>
                {{ mm_gamer_name($gamer_1, $act_compo) }}
                <input type="number" id="point_1" name="point_1" min="0" max="100">
                vs
                {{ mm_gamer_name($gamer_2, $act_compo) }}
                <input type="number" id="point_2" name="point_2" min="0" max="100">
                <button type="submit" name="match_action" value="finish" class="btn btn-success">Finish</button>
                <button type="submit" name="match_action" value="delete" class="btn btn-success">Delete</button>
            @csrf
        </form>
        @endif
    @endif
    </td>
</tr>
@endforeach
</table>

Befejezett meccsek:<br/>
@foreach ($finished_matches as $match)
    {{ $match->id }} : 
    {{ $match->competition->game->name }} : 
    {{ $match->game_station->name}} : 
    {{ $match->participations->get(0)->gamer->nickname }} 
    {{ $match->participations->get(0)->score}}:{{ $match->participations->get(1)->score}}
    {{ $match->participations->get(1)->gamer->nickname}} 
    <br/>
@endforeach
</x-app>