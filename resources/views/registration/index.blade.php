<x-app>
<div class="container">

    <h2>Játékos Regisztrálása</h2>
    <form action="{{ route('registration.entry_store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="player_name">Játékos Neve</label>
            <select name="gamer_id" id="gamer_id" class="form-control">
                <option value="0">-</option>
                @foreach ($gamers as $gamer)
                    <option value="{{ $gamer->id }}" {{ ($gamer->id == $last_gamer_id) ? 'selected' : ''}}>{{ $gamer->nickname }}</option>
                @endforeach
            </select>
            <input type="text" name="new_gamer_nickname" id="new_gamer_nickname" class="form-control">
        </div>
        <div class="form-group">
            <label for="competition_id">Versenyszám</label>
            <select name="competition_id" id="competition_id" class="form-control">
                @foreach ($competitions as $competition)
                    <option value="{{ $competition->id }}">{{ $competition->game->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">OK</button>
    </form>

    <h2>Nevezések</h2>
    <table class="table">
        <tbody>
            @foreach ($entries as $entry)
                <tr>
                    <td>{{ $entry->gamer->nickname }}</td>
                    <td>{{ $entry->competition->game->name }}</td>
                    <td>
                    <td>
                        <form action="{{ route('registration.entry_update') }}" method="POST">
                            <input type="hidden" name="entry_id" value="{{ $entry->id }}"/> 
                            <select name="entry_status" id="entry_status" class="form-control">
                            @foreach ($entry_status_options as $entry_status_option)
                                <option value="{{ $entry_status_option }}" {{ ($entry->status == $entry_status_option) ? 'selected' : '' }}>{{ $entry_status_option }}</option>
                            @endforeach
                            </select>
                            <input type="number" id="entry_points" name="entry_points" min="0" max="10" value="{{$entry->points}}">
                            @csrf
                            <button type="submit" class="btn btn-success">Update</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</x-app>