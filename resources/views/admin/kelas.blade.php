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
                <div class="p-6 text-gray-900 dark:text-gray-100">

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
                            <!-- Filter Berdasarkan Bulan -->
                            <select name="month" class="form-control mr-2">
                                <option value="">All Months</option>
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}"
                                        {{ request('month') == $month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                        <!-- Nama bulan dalam format teks -->
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
                            <!-- Filter Berdasarkan Tanggal -->
        <input type="date" name="date" class="form-control mr-2" 
        placeholder="Select Date" value="{{ request('date') }}">
                            <button type="submit" class="btn btn-secondary">Filter</button>
                        </div>
                    </form>

                    @if ($classes->isEmpty())
                        <p>No classes found.</p>
                    @else
                        <!-- Tambahkan kelas 'table-responsive' agar tabel bisa di-scroll secara horizontal -->
                        <div class="table-responsive" style="max-height: 360px; overflow-y: scroll;">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Class Category</th>
                                        <th>Coach</th>
                                        <th>Day</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($classes as $class)
                                        <tr>
                                            <td>{{ $class->name }}</td>
                                            <td>{{ $class->category->name ?? 'No Category' }}</td>
                                            <td>{{ $class->coach->name ?? 'No Coach' }}</td>
                                            <td>{{ $class->day_of_week }}</td>
                                            <td>{{ $class->date ? \Carbon\Carbon::parse($class->date)->format('d M Y') : 'No Date' }}
                                            </td> <!-- Tanggal kelas -->
                                            <td>
                                                <a href="#" class="btn btn-info btn-sm" data-toggle="modal"
                                                    data-target="#classDetailModal{{ $class->id }}">View Details</a>

                                                <!-- Modal -->
                                                <div class="modal fade" id="classDetailModal{{ $class->id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="classDetailModalLabel{{ $class->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="classDetailModalLabel{{ $class->id }}">Class
                                                                    Details: {{ $class->name }}</h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Tambahkan table-responsive untuk tabel di dalam modal -->
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered">
                                                                        <tr>
                                                                            <th>Class Image</th>
                                                                            <td>
                                                                                @if ($class->image)
                                                                                    <img src="{{ Storage::url($class->image) }}"
                                                                                        alt="Class Image"
                                                                                        style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                                                                                @else
                                                                                    No Image
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Class Name</th>
                                                                            <td>{{ $class->name }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Description</th>
                                                                            <td>{{ $class->description ?? 'No Description' }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Class Category</th>
                                                                            <td>{{ $class->category->name ?? 'No Category' }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Coach</th>
                                                                            <td>{{ $class->coach->name ?? 'No Coach' }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Day</th>
                                                                            <td>{{ $class->day_of_week }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Date</th>
                                                                            <td>{{ $class->date ? \Carbon\Carbon::parse($class->date)->format('d M Y') : 'No Date' }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Start Time</th>
                                                                            <td>{{ $class->start_time }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>End Time</th>
                                                                            <td>{{ $class->end_time }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Price</th>
                                                                            <td>Rp {{ number_format($class->price, 2) }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Quota</th>
                                                                            <td>{{ $class->quota }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Registered Count</th>
                                                                            <td>{{ $class->registered_count }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Room</th>
                                                                            <td>{{ $class->room->name ?? 'No Room' }}
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Close</button>
                                                                <a href="{{ route('admin.classes.edit', $class->id) }}"
                                                                    class="btn btn-warning btn-sm">Edit</a>
                                                                <form
                                                                    action="{{ route('admin.classes.delete', $class->id) }}"
                                                                    method="POST" style="display:inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-sm">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
