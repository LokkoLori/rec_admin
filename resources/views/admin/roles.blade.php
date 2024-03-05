
<x-app>
    {{-- List roles --}}
    @foreach ($roles as $role)
        <p>{{ $role->name }}</p>
    @endforeach

    {{-- Create a new role --}}
    <form action="{{ route('admin.roles.create') }}" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Role name">
        <button type="submit">Create</button>
    </form>
</x-app>