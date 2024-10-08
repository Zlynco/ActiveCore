<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Booking</h1>
        <a href="{{ route('admin.booking') }}" class="btn btn-secondary">Back to Bookings</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Tampilkan nama kelas yang di-booking -->
                        <div class="form-group">
                            <label for="class">Kelas</label>
                            <input type="text" class="form-control" value="{{ $booking->class->name }}" readonly>
                        </div>

                        <!-- Tanggal Booking -->
                        <div class="form-group">
                            <label for="booking_date">Tanggal Booking</label>
                            <input type="date" name="booking_date" id="booking_date" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') }}" required>
                        </div>

                        <!-- Status Pembayaran -->
                        <div class="form-group">
                            <label for="paid">Status Pembayaran</label>
                            <div class="form-check">
                                <input type="checkbox" name="paid" id="paid" class="form-check-input" value="1" 
                                       {{ $booking->paid ? 'checked' : '' }}>
                                <label class="form-check-label" for="paid">Booking sudah dibayar</label>
                            </div>
                        </div>

                        <!-- Tombol Update -->
                        <button type="submit" class="btn btn-primary">Update Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
