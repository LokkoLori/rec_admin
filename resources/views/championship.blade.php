
<h2 style="margin-bottom: 0px"> <img src="{{ asset('images/reclogo.png') }}" width="150"/> {{ $actual_season->year }} results </h2>

<div style="display: flex;">
@foreach ($compos as $compo)
<div style="border-right: 1px solid black; padding: 5px;">
<p>{{ $compo["name"] }}</p>
<table style="margin-right: 20px;">
    <thead>
    </thead>
        <tr>
            <th></th>
            <th></th>
            <th>score</th>
        </tr>
    <tbody>
    @php
        $rank = 1;
    @endphp
    @foreach ($compo["table"] as $element)
    <tr>
        <td>{{ $rank }}</td>
        <td>{{ $element->gamer_name }}</td>
        <td>{{ $element->total_points }}</td>
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
