<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Coach Booking</h1>
        <a href="{{ route('admin.booking') }}" class="btn btn-secondary">Back to Manage Booking</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.bookings.storeCoach') }}" method="POST" onsubmit="return validateBookingTime()">
                        @csrf
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select name="coach_id" id="coach_id" class="form-control" required aria-placeholder="Select Coach">

                                @foreach($coaches as $coach)
                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="booking_date">Booking Date</label>
                            <input type="date" name="booking_date" id="booking_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="start_booking_time">Start Booking Time</label>
                            <input type="time" name="start_booking_time" id="start_booking_time" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="end_booking_time">End Booking Time</label>
                            <input type="time" name="end_booking_time" id="end_booking_time" class="form-control" readonly required>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk memvalidasi waktu booking
        function validateBookingTime() {
            const startTime = document.getElementById('start_booking_time').value;
            const endTime = document.getElementById('end_booking_time').value;

            if (startTime >= endTime) {
                Swal.fire({
                    title: 'Error!',
                    text: 'End time must be later than start time.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false; // Mencegah pengiriman formulir
            }
            return true; // Mengizinkan pengiriman formulir
        }

        // Menangani perubahan waktu start_booking_time
        document.getElementById('start_booking_time').addEventListener('input', function() {
            var startTime = this.value;
            var endTime = calculateEndTime(startTime); // Hitung end time (1 jam setelah start time)
            document.getElementById('end_booking_time').value = endTime; // Update end time
        });

        // Fungsi untuk menghitung waktu end time (1 jam setelah start time)
        function calculateEndTime(startTime) {
            const start = startTime.split(':');
            let hour = parseInt(start[0]);
            const minute = parseInt(start[1]);

            // Tambahkan 1 jam ke start time
            hour += 1;

            if (hour === 24) {
                hour = 0; // Jika jam mencapai 24, reset ke jam 0 (midnight)
            }

            return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
        }
    </script>

    <!-- SweetAlert Alerts -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Sorry!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif
</x-appadmin-layout>
