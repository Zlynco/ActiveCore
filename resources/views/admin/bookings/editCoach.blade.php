<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Coach Booking</h1>
        <a href="{{ route('admin.booking') }}" class="btn btn-secondary">Back to Manage Booking</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.bookings.updateCoach', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select name="coach_id" id="coach_id" class="form-control" required>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}"
                                        {{ $booking->coach_id == $coach->id ? 'selected' : '' }}>{{ $coach->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="session_count">Session Count</label>
                            <input type="number" name="session_count" id="session_count" class="form-control"
                                value="{{ $booking->session_count }}" required>
                        </div>

                        <div class="form-group">
                            <label for="payment_required">Payment Required</label>
                            <select name="payment_required" id="payment_required" class="form-control" required>
                                <option value="1" {{ $booking->payment_required ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$booking->payment_required ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="booking_date">Booking Date</label>
                            <input type="date" name="booking_date" id="booking_date" class="form-control"
                                value="{{ $booking->booking_date ? $booking->booking_date->format('Y-m-d') : '' }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="start_booking_time">Booking Time</label>
                            <input type="time" name="start_booking_time" id="start_booking_time" class="form-control"
                                value="{{ $booking->start_booking_time ? \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_booking_time)->format('H:i') : '' }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="end_booking_time">Booking Time</label>
                            <input type="time" name="end_booking_time" id="end_booking_time" class="form-control"
                                value="{{ $booking->end_booking_time ? \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_booking_time)->format('H:i') : '' }}"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
