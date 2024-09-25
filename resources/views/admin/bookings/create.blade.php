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
                    <form action="{{ route('admin.bookings.store') }}" method="POST" id="bookingForm">
                        @csrf

                        <div class="form-group">
                            <label for="class">Pilih Kelas</label>
                            <select name="class_id" id="class" class="form-control">
                                @foreach ($classesWithDates as $classData)
                                    <option value="{{ $classData['class']->id }}">{{ $classData['class']->name }} - {{ $classData['class']->day_of_week }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="booking_date">Pilih Tanggal Sesuai Jadwal Kelas</label>
                            <select name="booking_date" id="booking_date" class="form-control">
                                @foreach ($classesWithDates as $classData)
                                    <optgroup label="{{ $classData['class']->name }}">
                                        @foreach ($classData['availableDates'] as $dateData)
                                            <option value="{{ $dateData['date'] }}">{{ $dateData['date'] }} - Sisa Kuota: {{ $dateData['available_quota'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Book Class</button>
                    </form>
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
