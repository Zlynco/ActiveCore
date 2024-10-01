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

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

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

                        <!-- Kategori -->
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control" required onchange="filterCoaches()">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $class->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
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
                            <label for="start_time">Waktu Mulai</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $class->start_time) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="end_time">Waktu Berakhir</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $class->end_time) }}" required>
                        </div>
                        
                        <!-- Harga -->
                        <div class="form-group">
                            <label for="price">Harga</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price', $class->price) }}" required>
                        </div>

                        <!-- Kuota -->
                        <div class="form-group">
                            <label for="quota">Kuota</label>
                            <input type="number" name="quota" class="form-control" value="{{ old('quota', $class->quota) }}" required>
                        </div>
                        
                        <!-- Coach -->
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select id="coach_id" name="coach_id" class="form-control" required>
                                <option value="">Select Coach</option>
                                @foreach ($coaches as $coach)
                                    @foreach ($coach->categories as $category)
                                        <option value="{{ $coach->id }}" data-category="{{ $category->id }}" {{ $class->coach_id == $coach->id ? 'selected' : '' }}>
                                            {{ $coach->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                                <option value="no_coach" class="no-coach-option" style="display: none;">No Coach Available</option>
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

    <script>
        function filterCoaches() {
        const categoryId = document.getElementById('category_id').value;
        const coachSelect = document.getElementById('coach_id');
        const noCoachOption = coachSelect.querySelector('.no-coach-option');
        let coachAvailable = false;

        // Mengambil semua opsi coach
        const coaches = coachSelect.querySelectorAll('option:not(.no-coach-option)');

        // Menampilkan atau menyembunyikan opsi pelatih berdasarkan kategori
        coaches.forEach(coach => {
            if (coach.getAttribute('data-category') == categoryId) {
                coach.style.display = "block"; // Tampilkan opsi
                coachAvailable = true; // Ada coach yang sesuai
            } else {
                coach.style.display = "none"; // Sembunyikan opsi
            }
        });

        // Menampilkan opsi "No Coach Available" jika tidak ada coach
        if (!coachAvailable) {
            noCoachOption.style.display = "block";
            coachSelect.value = "no_coach"; // Set value ke "No Coach Available"
        } else {
            noCoachOption.style.display = "none";
        }
    }

    // Panggil filterCoaches saat halaman dimuat untuk menampilkan coach yang relevan
    window.onload = function() {
        filterCoaches();
    }
    </script>
</x-appadmin-layout>
