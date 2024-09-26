<x-appadmin-layout>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Class</h1>
        <a href="{{ route('admin.kelas') }}" class="btn btn-secondary">Back to Manage Class</a>
    </div>

    <div class="py-0" >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" style="max-height: 500px; overflow-y: scroll;">
                    <form action="{{ route('admin.classes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group">
                            <label for="image">Class Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label for="name">Class Name</label>
                            <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control" required onchange="filterCoaches()">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="day_of_week">Day of the Week</label>
                            <select name="day_of_week" class="form-control" required>
                                <option value="">Select Day</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                            @error('day_of_week')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="time" name="start_time" class="form-control" required value="{{ old('start_time') }}">
                            @error('start_time')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input type="time" name="end_time" class="form-control" required value="{{ old('end_time') }}">
                            @error('end_time')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" required value="{{ old('price') }}">
                            @error('price')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="quota">Quota</label>
                            <input type="number" name="quota" id="quota" class="form-control" required value="{{ old('quota') }}">
                            @error('quota')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="coach_id">Coach</label>
                            <select id="coach_id" name="coach_id" class="form-control" required>
                                <option value="">Select Coach</option>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}" data-category="{{ $coach->category_id }}">{{ $coach->name }}</option>
                                @endforeach
                                <option value="no_coach" class="no-coach-option" style="display: none;">No Coach Available</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function filterCoaches() {
            const categoryId = document.getElementById('category_id').value;
            const coachSelect = document.getElementById('coach_id');
            const submitBtn = document.getElementById('submitBtn');
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
                submitBtn.disabled = true; // Disable submit button
            } else {
                noCoachOption.style.display = "none";
                submitBtn.disabled = false; // Enable submit button
            }
        }
    </script>
</x-appadmin-layout>
