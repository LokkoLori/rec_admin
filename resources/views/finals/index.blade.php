<div style="margin: 20px; font-family: sans-serif;">
    <h2>Finals Management</h2>

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

    @if(!$setupDone)
    <div style="border: 1px solid #ccc; padding: 20px; max-width: 400px;">        
        <form action="{{ route('finals.setup') }}" method="POST" onsubmit="return confirm('Are you sure you want to set up the finals?');">
            @csrf
            <button type="submit" style="padding: 10px 20px; cursor: pointer; font-size: 16px;">
                Setup Finals
            </button>
        </form>
    </div>
    @else
        <div style="border: 1px solid #ccc; padding: 20px; max-width: 600px;">
            <h3>Manage Competitions</h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #333;">
                        <th style="padding: 10px 0;">Competition</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($competitions as $compo)

                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px 0;">{{ $compo->game->name }}</td>
                            <td>
                                @if($compo->final_status === 'active')
                                    <span style="color: green; font-weight: bold;">ACTIVE</span>
                                @elseif($compo->final_status === 'finished')
                                    <span style="color: gray;">FINISHED</span>
                                @else
                                    <span style="color: darkorange; font-weight: bold;">PENDING</span>
                                @endif
                            </td>
                            <td>
                                @if($compo->final_status === 'pending')
                                    <form action="{{ route('finals.start_competition', $compo->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to start the finals for {{ $compo->game->name }}?');">
                                        @csrf
                                        <button type="submit" style="padding: 5px 10px; cursor: pointer; background-color: darkorange; color: white; border: none; border-radius: 3px;">Start</button>
                                    </form>
                                @elseif($compo->final_status === 'active')
                                    <a href="{{ route('finals.manage_matches', $compo->id) }}" style="display: inline-block; padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;">Manage Matches</a>
                                    
                                    <form action="{{ route('finals.close_competition', $compo->id) }}" method="POST" style="display: inline-block; margin-left: 10px;" onsubmit="return confirm('Are you sure you want to close this competition? OBS screens will be cleared.');">
                                        @csrf
                                        <button type="submit" style="padding: 5px 10px; cursor: pointer; background-color: #dc3545; color: white; border: none; border-radius: 3px;">Close</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>