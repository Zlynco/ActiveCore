<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Room</h1>
        <div class="d-flex mt-3 mt-sm-0">
            <a href="{{ route('admin.room.create') }}" class="btn btn-primary">Add New Room</a>
            <a href="{{ route('admin.room.logs') }}" class="btn btn-info ml-2">Show Room Log</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" style="max-height: 500px; overflow-y: scroll;">
                    <form method="GET" action="{{ route('admin.rooms') }}">
                        <div class="mb-4">
                            <x-text-input id="search" name="search" type="text" placeholder="Search Class..."
                                :value="request('search')" class="form-control" />
                            <x-input-error :messages="$errors->get('search')" class="mt-2" />
                        </div>
                    </form>
                    @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Capacity</th>
                            <th>Equipment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rooms as $room)
                            <tr>
                                <td>{{ $room->name }}</td>
                                <td>{{ $room->capacity }}</td>
                                <td>{{ $room->equipment }}</td>
                                <td>
                                    <a href="{{ route('admin.room.edit', $room->id) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('admin.room.destroy', $room->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
