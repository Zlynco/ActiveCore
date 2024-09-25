<x-appadmin-layout>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
        <a href="{{ route('admin.users.logs') }}" class="btn btn-info ml-2">Show Users Log</a>
    </div>
    <div class="container-fluid mt-4 ">
        <!-- Tab navigation -->
        <ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="members-tab" data-bs-toggle="tab" href="#members" role="tab"
                    aria-controls="members" aria-selected="true">Members</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="coaches-tab" data-bs-toggle="tab" href="#coaches" role="tab"
                    aria-controls="coaches" aria-selected="false">Coaches</a>
            </li>
            <form method="GET" action="{{ route('admin.user') }}">
                <div class="mb-4">
                    <x-text-input id="search" name="search" type="text" placeholder="Search users..."
                        :value="request('search')" class="form-control" />
                    <x-input-error :messages="$errors->get('search')" class="mt-2" />
                </div>
            </form>
        </ul>
        <!-- Search Form -->

        <div class="tab-content" id="userTabsContent" style="max-height: 500px; overflow-y: scroll;">
            <div class="tab-pane fade show active" id="members" role="tabpanel" aria-labelledby="members-tab">
                <!-- Konten untuk Members -->
                @include('admin.partials.members')
            </div>
            <div class="tab-pane fade" id="coaches" role="tabpanel" aria-labelledby="coaches-tab">
                <!-- Konten untuk Coaches -->
                @include('admin.partials.coaches')
            </div>
        </div>

    </div>

</x-appadmin-layout>
