<x-app>
<div class="container">
    <h1>Nevezés a Versenyre</h1>
    <form action="{{ route('entries.store') }}" method="POST">
        @csrf

        @foreach ($competitions as $competition)
        <input type="hidden" name="competitions[{{ $competition->id }}]" value="0">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="competitions[{{ $competition->id }}]" value="1" {{ $userEntries->contains('competition_id', $competition->id) ? 'checked' : '' }}>
            <label class="form-check-label">
                {{ $competition->competitionDay->date }} -{{ $competition->game->name }}
            </label>
        </div>
        @endforeach

        <div class="form-group">
            <label for="note">Megjegyzés (opcionális):</label>
            <textarea name="note" id="note" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Nevezések frissítése</button>
    </form>
</div>
</x-app>