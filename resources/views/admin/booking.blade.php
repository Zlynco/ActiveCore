<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Bookings</h1>
        <div class="d-flex mt-3 mt-sm-0">
            <a id="addNewBookingBtn" href="{{ route('admin.bookings.create') }}" class="btn btn-primary">Add New
                Booking</a>
            <a href="{{ route('admin.bookings.logs') }}" class="btn btn-info ml-2">Show Booking Log</a>
        </div>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Tab navigation -->
                    <ul class="nav nav-tabs" id="bookingTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="class-tab" data-bs-toggle="tab" href="#class-bookings"
                                role="tab" aria-controls="class-bookings" aria-selected="true">Class Bookings</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="coach-tab" data-bs-toggle="tab" href="#coach-bookings"
                                role="tab" aria-controls="coach-bookings" aria-selected="false">Coach Bookings</a>
                        </li>
                        <form method="GET" action="{{ route('admin.booking') }}">
                            <div class="mb-1 ml-2">
                                <x-text-input id="search" name="search" type="text"
                                    placeholder="Search booking..." :value="request('search')" class="form-control" />
                                <x-input-error :messages="$errors->get('search')" class="mt-2" />
                            </div>
                        </form>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content" id="bookingTabContent" style="max-height: 400px; overflow-y: scroll;">
                        <!-- Class bookings tab -->
                        <div class="tab-pane fade show active table-responsive" id="class-bookings" role="tabpanel"
                            aria-labelledby="class-tab">
                            @if ($bookings->isEmpty())
                                <p>No bookings found.</p>
                            @else
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama Kelas</th>
                                            <th>Coach</th>
                                            <th>Member</th>
                                            <th>Jadwal Kelas</th>
                                            <th>Booking Date</th>
                                            <th>Amount</th>
                                            <th>Paid</th>
                                            <th>Quota Filled</th>
                                            <th>Booking Code</th>
                                            <th>QR Code</th> <!-- Menambahkan kolom QR Code -->
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bookings as $booking)
                                            <tr>
                                                <td>{{ $booking['class_name'] }}</td>
                                                <td>{{ $booking['coach_name'] }}</td>
                                                <td>{{ $booking['member_name'] }}</td>
                                                <td>{{ $booking['day_of_week'] }} at {{ $booking['start_time'] }} -
                                                    {{ $booking['end_time'] }}</td>
                                                <td>{{ $booking['booking_date'] }}</td>
                                                <td>Rp{{ $booking['amount'] }}</td>
                                                <td>{{ $booking['paid'] }}</td>
                                                <td>{{ $booking['quota_filled'] }} / {{ $booking['quota'] }}</td>
                                                <td>{{ $booking['booking_code'] }}</td>
                                                <td>
                                                    <!-- Tombol untuk membuka modal QR Code -->
                                                    <button class="btn btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#qrCodeModal"
                                                        data-booking-code="{{ $booking['booking_code'] }}">
                                                        Show QR Code
                                                    </button>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.bookings.edit', $booking['id']) }}"
                                                        class="btn btn-warning">Edit</a>
                                                    <form
                                                        action="{{ route('admin.bookings.destroy', $booking['id']) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="qrCodeModalLabel">QR Code</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="qrCodeContent"></div> <!-- Tempat untuk menampilkan QR Code -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade table-responsive" id="coach-bookings" role="tabpanel" aria-labelledby="coach-tab">
                            @if ($coachBookings->isEmpty())
                                <p>No coach bookings found.</p>
                            @else
                            <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Coach</th>
                                            <th>Member</th>
                                            <th>Session Count</th>
                                            <th>Booking Date</th>
                                            <th>start & end time</th>
                                            <th>Payment Required</th>
                                            <th>Booking Code</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($coachBookings as $booking)
                                            <tr>
                                                <td>{{ $booking->coach->name }}</td>
                                                <td>{{ $booking->member->name }}</td>
                                                <td>{{ $booking->session_count }}</td>
                                                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') }}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($booking->start_booking_time)->format('H:i') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($booking->end_booking_time)->format('H:i') }}
                                                </td>
                                                <td>{{ $booking->payment_required ? 'Yes' : 'No' }}</td>
                                                <td>{{ $booking->booking_code }}</td>
                                                <td>
                                                    <a href="{{ route('admin.bookings.editCoach', $booking->id) }}"
                                                        class="btn btn-warning">Edit</a>
                                                    <form
                                                        action="{{ route('admin.bookings.destroyCoach', $booking->id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addNewBookingBtn = document.getElementById('addNewBookingBtn');
            var tabs = document.querySelectorAll('#bookingTab a[data-bs-toggle="tab"]');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var activeTabId = tab.getAttribute('href').substring(1);
                    if (activeTabId === 'coach-bookings') {
                        addNewBookingBtn.setAttribute('href',
                            '{{ route('admin.bookings.createCoach') }}');
                    } else {
                        addNewBookingBtn.setAttribute('href',
                            '{{ route('admin.bookings.create') }}');
                    }
                });
            });

            // Trigger click event on the default active tab to set the initial state of the button
            var activeTab = document.querySelector('#bookingTab .nav-link.active');
            if (activeTab) {
                var activeTabId = activeTab.getAttribute('href').substring(1);
                if (activeTabId === 'coach-bookings') {
                    addNewBookingBtn.setAttribute('href', '{{ route('admin.bookings.createCoach') }}');
                }
            }
        });
    </script>
<script>
    // Event listener untuk tombol yang memicu modal
    document.addEventListener('DOMContentLoaded', function() {
        const qrCodeModal = document.getElementById('qrCodeModal');
        qrCodeModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Tombol yang memicu modal
            const bookingCode = button.getAttribute('data-booking-code'); // Ambil booking code
            const qrCodeHtml = generateQRCode(bookingCode);
            document.getElementById('qrCodeContent').innerHTML = qrCodeHtml;
        });
    });

    // Fungsi untuk menghasilkan QR code
    function generateQRCode(bookingCode) {
        const qrCodeUrl = `{{ asset('qrcodes/QR-${bookingCode}.png') }}`; // Menggunakan Laravel asset helper
        return `<img src="${qrCodeUrl}" alt="QR Code" style="width: 100%; height: auto;">`;
    }
</script>
</x-appadmin-layout>
