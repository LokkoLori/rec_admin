<!--script>
    setTimeout(function() {
        window.location.reload(true); 
    }, 10000);
</script-->

<h1> REC {{ $actual_day->season->year}} - {{ $actual_day->name }} </h1>
<h2> ponttáblázat </h2>

<div style="display: flex;">
@foreach ($compo_score_tables as $table)
<div>
<h3>{{ $table["compo"]->game->name }}</h3>
<table style="margin-right: 20px;">
    <thead>
    </thead>
        <tr>
            <th>rank</th>
            <th>név (meccsek száma)</th>
            <th>primary</th>
            <th>seconary</th>
        </tr>
    <tbody>
    @php
        $rank = 1;
    @endphp
    @foreach ($table["gamer_data"] as $gamer_data)
    <tr>
        <td>{{ $rank }}.</td>
        <td>{{ $gamer_data["gamer"]->nickname }}({{ count($gamer_data["matches"]) }})</td>
        <td>{{ $gamer_data["primary_score"]}}</td>
        <td>{{ $gamer_data["secondary_score"]}}</td>
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

