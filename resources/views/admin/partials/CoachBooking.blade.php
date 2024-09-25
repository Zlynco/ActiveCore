<form action="{{ route('admin.coach.bookings.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="coach">Select Coach:</label>
        <select name="coach_id" id="coach" class="form-control">
            @foreach($coaches as $coach)
                <option value="{{ $coach->id }}">{{ $coach->name }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Book Coach</button>
</form>

@if ($coachBooking && $coachBooking->payment_required)
    <div class="alert alert-warning">
        Payment is required after 4 sessions. Please complete the payment to continue.
    </div>
@endif
