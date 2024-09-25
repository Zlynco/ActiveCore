<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Class</h1>
        <a href="{{ route('admin.kelas') }}" class="btn btn-secondary">Back to Manage Class</a>
    </div>

    <div class="py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.classes.update', $class->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                    
                        <!-- Nama Kelas -->
                        <div class="form-group">
                            <label for="name">Nama Kelas</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $class->name) }}" required>
                        </div>
                    
                        <!-- Deskripsi Kelas -->
                        <div class="form-group">
                            <label for="description">Deskripsi Kelas</label>
                            <textarea name="description" class="form-control" required>{{ old('description', $class->description) }}</textarea>
                        </div>
                    
                        <!-- Hari -->
                        <div class="form-group">
                            <label for="day_of_week">Hari</label>
                            <select name="day_of_week" class="form-control" required>
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                    <option value="{{ $day }}" {{ old('day_of_week', $class->day_of_week) == $day ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    
                        <!-- Waktu -->
                        <div class="form-group">
                            <label for="time">Waktu</label>
                            <input type="time" name="time" class="form-control" value="{{ old('time', $class->time) }}" required>
                        </div>
                    
                        <!-- Harga -->
                        <div class="form-group">
                            <label for="price">Harga</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price', $class->price) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="quota">Quota</label>
                            <input type="number" name="quota" class="form-control" value="{{ old('quota', $class->quota) }}" required>
                        </div>
                        
                        <!-- Coach -->
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select name="coach_id" class="form-control" required>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}" {{ $class->coach_id == $coach->id ? 'selected' : '' }}>
                                        {{ $coach->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    
                        <!-- Gambar -->
                        <div class="form-group">
                            <label for="image">Gambar</label>
                            @if($class->image)
                                <div>
                                    <img src="{{ Storage::url($class->image) }}" alt="Class Image" width="100">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control">
                        </div>
                    
                        <button type="submit" class="btn btn-primary">Update Class</button>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</x-appadmin-layout>
