<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pending Request Details</h1>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <strong>User:</strong> {{ $request->user->name }}
                    </div>
                    <div class="mb-4">
                        <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                    </div>
                    <div class="mb-4">
                        <strong>Details:</strong> {{ $request->details }}
                    </div>
                    @if($request->type === 'coach_application')
                        <div class="mb-4">
                            <strong>Additional Information:</strong> {{ $request->additional_information }}
                        </div>
                    @endif
                    <div class="mb-4">
                        <strong>Status:</strong> {{ ucfirst($request->status) }}
                    </div>
                    <div class="mt-4">
                        <form action="{{ route('admin.pending_request.approve', $request->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                        <form action="{{ route('admin.pending_request.reject', $request->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                        <a href="{{ route('admin.request') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
