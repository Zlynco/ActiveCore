@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Payment for Booking</h2>
    <p><strong>Class:</strong> {{ $booking->class->name }}</p>
    <p><strong>Booking Date:</strong> {{ $booking->booking_date }}</p>
    <p><strong>Amount Due:</strong> {{ $booking->amount }}</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.payment.process', $booking->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <input type="text" class="form-control" id="payment_method" name="payment_method" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" class="form-control" id="amount" name="amount" value="{{ $booking->amount }}" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Confirm Payment</button>
    </form>
</div>
@endsection
