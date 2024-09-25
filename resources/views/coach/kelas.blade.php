@extends('layouts.appcoach')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Your Classes</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body" style="max-height: 500px; overflow-y: scroll;">
                @if($classes->count() > 0)
                    <ul class="list-group">
                        @foreach($classes as $class)
                            <li class="list-group-item">
                                <strong>{{ $class->name }}</strong><br>
                                Hari: Setiap {{ $class->day_of_week }}<br>
                                Waktu: {{ $class->time }}<br>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>No classes registered yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
