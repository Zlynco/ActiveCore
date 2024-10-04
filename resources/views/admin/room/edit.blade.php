<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Room</h1>
        <a href="{{ route('admin.rooms') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.room.update', $room->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Room Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $room->name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <input type="number" name="capacity" id="capacity" class="form-control" value="{{ $room->capacity }}" required>
                        </div>

                        <div class="form-group">
                            <label for="equipment">Equipment</label>
                            <textarea name="equipment" id="equipment" class="form-control">{{ $room->equipment }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Room</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
