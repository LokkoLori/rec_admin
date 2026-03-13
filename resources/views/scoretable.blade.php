<h2 style="margin-bottom: 0px"> <img src="{{ asset('images/reclogo.png') }}" width="150"/> {{ $actual_day->season->year}} - {{ $actual_day->name }} </h2>

<div style="display: flex;">
@foreach ($compo_score_tables as $table)
@continue($hide_combined && str_starts_with($table["compo"]->game->name, 'Combined'))
<div style="border-right: 1px solid black; padding: 5px;">
<p>{{ $table["compo"]->game->name }}</p>
<table style="margin-right: 20px;">
    <thead>
    </thead>
        <tr>
            <th></th>
            <th align="left">nick</th>
            <th>chp</th>
            <th>main</th>
            <th>sub</th>
            <th>mc</th>
        </tr>
    <tbody>
    @php
        $rank = 1;
    @endphp
    @foreach ($table["gamer_data"] as $gamer_data)
    <tr>
        <td>{{ ($gamer_data["qualified"] != 0) ? $rank.'.' : 'out' }}</td>
        <td>{{ $gamer_data["gamer"]->nickname }} {!! ($gamer_data["gamer"]->u14 == 1) ? "<font size=0.5><b>u</b></font>" : ""!!} {!! ($gamer_data["gamer"]->women == 1) ? "<font size=0.5><b>w</b></font>" : ""!!}</td>
        <td>{{ $gamer_data["points"]}}</td>
        <td>{{ $gamer_data["primary_score"]}}</td>
        <td>{{ $gamer_data["secondary_score"]}}</td>
        <td align="right">{{ count($gamer_data["matches"]) }}</td>
    </tr>
    @php
        $rank++;
    @endphp
    @endforeach
    </tbody>
</table>
</div>
@endforeach
</div>

