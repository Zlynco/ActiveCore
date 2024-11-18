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
                            <select name="coach_id" id="coach_id" class="form-control" required>
                                <option value="">Select Coach</option>
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
                            <select name="start_booking_time" id="start_booking_time" class="form-control" required>
                                <option value="">Select Time</option>
                            </select>


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
        // Validasi waktu booking
        function validateBookingTime() {
            const startTime = document.getElementById('start_booking_time').value;
            const endTime = document.getElementById('end_booking_time').value;

            if (!startTime) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a start time.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            if (startTime >= endTime) {
                Swal.fire({
                    title: 'Error!',
                    text: 'End time must be later than start time.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            return true;
        }

        // Fetch available times dynamically
        document.getElementById('coach_id').addEventListener('change', fetchAvailableTimes);
        document.getElementById('booking_date').addEventListener('change', fetchAvailableTimes);

        // Fetch available times dynamically and set min/max for time input
        function fetchAvailableTimes() {
    const coachId = document.getElementById('coach_id').value;
    const bookingDate = document.getElementById('booking_date').value;

    if (coachId && bookingDate) {
        fetch(`/api/gettime?coach_id=${coachId}&booking_date=${bookingDate}`)
            .then(response => response.json())
            .then(data => {
                const startTimeSelect = document.getElementById('start_booking_time');
                startTimeSelect.innerHTML = '<option value="">Select Time</option>'; // Reset options

                if (data.length === 0) {
                    Swal.fire({
                        title: 'No Available Times',
                        text: 'The selected coach has no available times for this date.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                data.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    startTimeSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching available times:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Could not fetch available times. Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    }
}

// Update end time when start time changes
document.getElementById('start_booking_time').addEventListener('change', function () {
    const startTime = this.value;
    if (startTime) {
        const endTime = calculateEndTime(startTime);
        document.getElementById('end_booking_time').value = endTime;
    } else {
        document.getElementById('end_booking_time').value = '';
    }
});


        // Calculate end time (1 hour after start time)
        function calculateEndTime(startTime) {
            const [hour, minute] = startTime.split(':').map(Number);
            const endHour = (hour + 1) % 24; // Add 1 hour, wrap around if >= 24
            return `${String(endHour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
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

{{-- <div class="form-group">
    <label for="start_booking_time">Start Booking Time</label>
    <select name="start_booking_time" id="start_booking_time" class="form-control" required>
        <option value="">Select Time</option>
    </select>
</div> --}}
{{-- function fetchAvailableTimes() {
    const coachId = document.getElementById('coach_id').value;
    const bookingDate = document.getElementById('booking_date').value;

    if (coachId && bookingDate) {
        fetch(`/api/gettime?coach_id=${coachId}&booking_date=${bookingDate}`)
            .then(response => response.json())
            .then(data => {
                const startTimeSelect = document.getElementById('start_booking_time');
                startTimeSelect.innerHTML = '<option value="">Select Time</option>'; // Reset options

                if (data.length === 0) {
                    Swal.fire({
                        title: 'No Available Times',
                        text: 'The selected coach has no available times for this date.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                data.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    startTimeSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching available times:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Could not fetch available times. Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    }
}

// Update end time when start time changes
document.getElementById('start_booking_time').addEventListener('change', function () {
    const startTime = this.value;
    if (startTime) {
        const endTime = calculateEndTime(startTime);
        document.getElementById('end_booking_time').value = endTime;
    } else {
        document.getElementById('end_booking_time').value = '';
    }
}); --}}
