<x-appadmin-layout>
    <!-- Tampilkan alert jika kuota penuh -->
    @if (session('quota_full'))
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Kuota Penuh!',
                text: 'Maaf, kuota kelas ini sudah penuh. Silakan pilih tanggal atau kelas lain.',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Booking</h1>
        <a href="{{ route('admin.booking') }}" class="btn btn-secondary">Back to Manage Booking</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Form Pencarian dan Filter -->
                    <form action="{{ route('admin.bookings.create') }}" method="GET">
                        <div class="form-row mb-3">
                            <div class="col">
                                <select name="category" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col">
                                <input type="date" name="booking_date" class="form-control"
                                    value="{{ request('booking_date') }}">
                            </div>

                            <div class="col">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                    <hr>

                    <!-- Tampilkan Kelas dalam Bentuk Card -->
                    <!-- Tampilkan Kelas dalam Bentuk Card -->
                    <div class="row" style="max-height: 400px; overflow-y: scroll;">
                        @if (empty($classesWithDates))
                            <div class="col-12">
                                <p>No classes available.</p>
                            </div>
                        @else
                            @foreach ($classesWithDates as $classData)
                                <div class="col-md-4">
                                    <div class="card mb-4">
                                        @if ($classData['class']->image)
                                            <img src="{{ Storage::url($classData['class']->image) }}"
                                                class="card-img-top" alt="{{ $classData['class']->name }}">
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $classData['class']->name }}</h5>
                                            <p class="card-text">Day: {{ $classData['class']->day_of_week }}</p>
                                            <p class="card-text">Available Date: {{ $classData['date'] }} - Sisa Kuota:
                                                {{ $classData['available_quota'] }}</p>
                                            <p class="card-text">Price: Rp
                                                {{ number_format($classData['class']->price, 0, ',', '.') }}</p>
                                            <!-- Menampilkan harga kelas -->

                                            <!-- Tautan untuk Booking -->
                                            <form action="{{ route('admin.bookings.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="class_id"
                                                    value="{{ $classData['class']->id }}">
                                                <input type="hidden" name="booking_date"
                                                    value="{{ $classData['date'] }}">
                                                <button type="submit" class="btn btn-primary">Book Class</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelector("#bookingForm").addEventListener("submit", function(e) {
            var selectedClassId = document.querySelector("#class").value;
            var selectedDate = document.querySelector("#booking_date").value;

            var options = document.querySelectorAll("#booking_date option");
            var quotaFilled = 0;

            options.forEach(function(option) {
                if (option.value === selectedDate) {
                    quotaFilled = parseInt(option.textContent.match(/Sisa Kuota: (\d+)/)[1]);
                }
            });

            if (quotaFilled <= 0) {
                e.preventDefault(); // Mencegah form submit
                Swal.fire({
                    icon: 'error',
                    title: 'Kuota Penuh!',
                    text: 'Kuota untuk kelas ini sudah penuh.',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
</x-appadmin-layout>
