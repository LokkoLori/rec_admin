<x-app>
@foreach ($finished_matches as $match)
    {{ $match->id }} : 
    {{ $match->competition->game->name }} : 
    {{ $match->game_station->name}} :

    @php
    $p0 = $match->participations->get(0);
    $p1 = $match->participations->get(1);
    @endphp
    <form action="{{ route('matches.update') }}" method="POST">
        <input type="hidden" name="match_id" value="{{ $match->id }}"/>
        <input type="hidden" name="participation_0_id" value="{{ $p0->id }}"/>
        <input type="hidden" name="participation_1_id" value="{{ $p1->id }}"/> 
        {{ $p0->gamer->nickname }} 
        <input type="number" id="participation_0_score" name="participation_0_score" min="0" max="10" value="{{ $p0->score }}">
        :
        <input type="number" id="participation_1_score" name="participation_1_score" min="0" max="10" value="{{ $p1->score }}">
        {{ $p1->gamer->nickname}} 
        @csrf
        <button type="submit" name="match_action" value="update" class="btn btn-success">update</button>
        <button type="submit" name="match_action" value="cancel" class="btn btn-success">cancel</button>
    </form>
    <br/>
@endforeach
</x-app>