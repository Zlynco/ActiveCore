<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Log Aktivitas Room</h1>
        <a href="{{ route('admin.rooms') }}" class="btn btn-secondary">Back to Manage Room</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" style="max-height: 500px; overflow-y: scroll;">
                    @if (count($logs) > 0)
                        <table class="table table-bordered table-striped" >
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Environment</th>
                                    <th>Level</th>
                                    <th>Message</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log['timestamp'] }}</td>
                                        <td>{{ $log['environment'] }}</td>
                                        <td>{{ $log['level'] }}</td>
                                        <td>{{ $log['message'] }}</td>
                                        <td>
                                            @if ($log['data'])
                                                <pre>{{ json_encode($log['data'], JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No logs available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
