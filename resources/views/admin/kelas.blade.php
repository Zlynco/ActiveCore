<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Classes</h1>
        <div class="d-flex mt-3 mt-sm-0">
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">Add New Class</a>
            <a href="{{ route('admin.classes.logs') }}" class="btn btn-info ml-2">Show Class Log</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" style="max-height: 500px; overflow-y: scroll;">
                    <form method="GET" action="{{ route('admin.kelas') }}">
                        <div class="mb-4">
                            <x-text-input id="search" name="search" type="text" placeholder="Search Class..."
                                :value="request('search')" class="form-control" />
                            <x-input-error :messages="$errors->get('search')" class="mt-2" />
                        </div>
                    </form>
                    @if ($classes->isEmpty())
                        <p>No classes found.</p>
                    @else
                        @php
                            $daysOfWeek = [
                                'Sunday' => 'Sunday',
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                            ];
                        @endphp
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class Image</th>
                                    <th>Class Name</th>
                                    <th>Class Category</th> <!-- Kolom Kategori -->
                                    <th>Coach</th>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Price</th>
                                    <th>Quota</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classes as $class)
                                    <tr>
                                        <td>
                                            @if ($class->image)
                                                <img src="{{ Storage::url($class->image) }}" alt="Class Image"
                                                    style="width: 75px; height: auto;">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->category->name ?? 'No Category' }}</td> <!-- Menampilkan kategori -->
                                        <td>{{ $class->coach->name }}</td>
                                        <td>{{ $class->day_of_week }}</td>
                                        <td>{{ $class->start_time }}</td>
                                        <td>{{ $class->end_time }}</td>
                                        <td>${{ $class->price }}</td>
                                        <td>{{ $class->quota }}</td>
                                        <td>
                                            <a href="{{ route('admin.classes.edit', $class->id) }}"
                                                class="btn btn-warning">Edit</a>
                                            <form action="{{ route('admin.classes.delete', $class->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
