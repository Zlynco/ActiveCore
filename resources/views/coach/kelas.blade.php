@extends('layouts.appcoach')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow mb-0">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Your Classes</h6>

                    </div>
                    <!-- Form Filter -->
                    <form method="GET" action="{{ route('coach.kelas') }}" class="p-4 m-0">
                        <div class="mb-0 d-flex">
                            <input type="date" name="date" class="form-control mr-2" value="{{ request('date') }}">

                            <select name="category" class="form-control mr-2">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-secondary">Filter</button>
                        </div>
                    </form>

                    <div class="card-body">
                        <div class="row" id="classList" style="max-height: 380px; overflow-y: scroll;">
                            @foreach ($classes as $class)
                                <div class="col-sm-6 col-md-4 mb-4 class-item" data-name="{{ strtolower($class->name) }}">
                                    <div class="card shadow">
                                        @if ($class->image)
                                            <img src="{{ Storage::url($class->image) }}" class="card-img-top"
                                                alt="{{ $class->name }}" style="height: 200px; object-fit: cover;">
                                        @else
                                        
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $class->name }}</h5>
                                            <p class="card-text">Date:
                                                {{ \Carbon\Carbon::parse($class->date)->format('d M Y') }}</p>
                                            <p class="card-text">Quota: {{ $class->quota }}</p>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#classDetailModal{{ $class->id }}">
                                                Details
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Modal for Class Details -->
                                    <div class="modal fade" id="classDetailModal{{ $class->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="classDetailModalLabel{{ $class->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="classDetailModalLabel{{ $class->id }}">
                                                        Class Details: {{ $class->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    @if ($class->image)
                                                        <img src="{{ Storage::url($class->image) }}" class="img-fluid mb-3"
                                                            alt="{{ $class->name }}">
                                                    @endif
                                                    <p><strong>Description:</strong>
                                                        {{ $class->description ?? 'No Description' }}</p>
                                                    <p><strong>Coach:</strong>
                                                        {{ $class->coach->name ?? 'No Coach' }}</p>
                                                    <p><strong>Day:</strong> {{ $class->day_of_week }}</p>
                                                    <p><strong>Date:</strong>
                                                        {{ \Carbon\Carbon::parse($class->date)->format('d M Y') }}</p>
                                                    <p><strong>Start Time:</strong> {{ $class->start_time }}</p>
                                                    <p><strong>End Time:</strong> {{ $class->end_time }}</p>
                                                    <p><strong>Price:</strong> Rp {{ number_format($class->price, 2) }}</p>
                                                    <p><strong>Quota:</strong> {{ $class->quota }}</p>
                                                    <p><strong>Registered Count:</strong> {{ $class->registered_count }}
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
