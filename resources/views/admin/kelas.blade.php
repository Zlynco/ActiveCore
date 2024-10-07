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

                    <!-- Form Filter & Search -->
                    <form method="GET" action="{{ route('admin.kelas') }}">
                        <div class="mb-4 d-flex">
                            <x-text-input id="search" name="search" type="text" placeholder="Search Class..."
                                :value="request('search')" class="form-control mr-2" />

                            <!-- Filter Berdasarkan Kategori -->
                            <select name="category" class="form-control mr-2">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Filter Berdasarkan Hari -->
                            <select name="day" class="form-control mr-2">
                                <option value="">All Days</option>
                                @foreach ($daysOfWeek as $day)
                                    <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-secondary">Filter</button>
                        </div>
                    </form>

                    @if ($classes->isEmpty())
                        <p>No classes found.</p>
                    @else
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>Class Image</th>
                                    <th>Class Name</th>
                                    <th>Description</th> <!-- Tambahan untuk deskripsi -->
                                    <th>Class Category</th>
                                    <th>Coach</th>
                                    <th>Day</th>
                                    <th>Date</th> <!-- Tambahan untuk tanggal -->
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Price</th>
                                    <th>Quota</th>
                                    <th>Registered Count</th> <!-- Tambahan untuk jumlah pendaftar -->
                                    <th>Room</th> <!-- Tambahan untuk ruangan -->
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classes as $class)
                                    <tr>
                                        <td>
                                            @if ($class->image)
                                                <img src="{{ Storage::url($class->image) }}" alt="Class Image"
                                                    style="width: 75px; height: 75px; object-fit: cover; border-radius: 5px;">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->description ?? 'No Description' }}</td> <!-- Deskripsi kelas -->
                                        <td>{{ $class->category->name ?? 'No Category' }}</td>
                                        <td>{{ $class->coach->name ?? 'No Coach' }}</td>
                                        <td>{{ $class->day_of_week }}</td>
                                        <td>{{ $class->date ? \Carbon\Carbon::parse($class->date)->format('d M Y') : 'No Date' }}
                                        </td> <!-- Tanggal kelas -->
                                        <td>{{ $class->start_time }}</td>
                                        <td>{{ $class->end_time }}</td>
                                        <td>${{ number_format($class->price, 2) }}</td>
                                        <td>{{ $class->quota }}</td>
                                        <td>{{ $class->registered_count }}</td> <!-- Jumlah pendaftar -->
                                        <td>{{ $class->room->name ?? 'No Room' }}</td> <!-- Ruangan -->
                                        <td>
                                            <a href="{{ route('admin.classes.edit', $class->id) }}"
                                                class="btn btn-warning btn-sm">Edit</a>
                                            <form action="{{ route('admin.classes.delete', $class->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Tambahkan paginasi jika diperlukan -->
                    <div class="mt-4">
                        {{ $classes->links() }} <!-- Paginasi -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
