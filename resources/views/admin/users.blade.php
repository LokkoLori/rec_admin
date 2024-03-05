<x-app>
<h1>Handling Users</h1>

@foreach ($users as $user)
    <form action="{{ route('admin.users.assignRole', $user) }}" method="POST">
        @csrf
        <div>
            <strong>{{ $user->name }}</strong>
        </div>
        <div>
            @foreach ($roles as $role)
                <div>
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                    <label>{{ $role->name }}</label>
                </div>
            @endforeach
        </div>
        <button type="submit">Update roles</button>
    </form>
@endforeach
</x-app>
