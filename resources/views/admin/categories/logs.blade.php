<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Log Aktivitas Booking</h1>
        <a href="{{ route('admin.category') }}" class="btn btn-secondary">Back to Manage Booking</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <pre style="max-height: 400px; overflow-y: scroll;">{{ $logs }}</pre>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
