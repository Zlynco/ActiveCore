<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Coach Booking</h1>
        <a href="{{ route('admin.booking') }}" class="btn btn-secondary">Back to Manage Booking</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.bookings.storeCoach') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select name="coach_id" id="coach_id" class="form-control" required>
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
                            <label for="start_booking_time">Booking Time</label>
                            <input type="time" name="start_booking_time" id="start_booking_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end_booking_time">Booking Time</label>
                            <input type="time" name="end_booking_time" id="end_booking_time" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
