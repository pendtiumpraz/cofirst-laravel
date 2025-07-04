<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tambah Kurikulum Baru') }}
            </h2>
            <a href="{{ route('admin.curriculums.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Kembali') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Terjadi kesalahan!</strong>
                            <ul class="mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.curriculums.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Curriculum Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Kurikulum <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                       required placeholder="Masukkan nama kurikulum">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Course Selection -->
                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700">Kursus <span class="text-red-500">*</span></label>
                                <select name="course_id" id="course_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                        required>
                                    <option value="">Pilih Kursus</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea name="description" id="description" rows="4" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Masukkan deskripsi kurikulum...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Learning Objectives -->
                            <div>
                                <label for="learning_objectives" class="block text-sm font-medium text-gray-700">Tujuan Pembelajaran</label>
                                <textarea name="learning_objectives" id="learning_objectives" rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Masukkan tujuan pembelajaran...">{{ old('learning_objectives') }}</textarea>
                                @error('learning_objectives')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Prerequisites -->
                            <div>
                                <label for="prerequisites" class="block text-sm font-medium text-gray-700">Prasyarat</label>
                                <textarea name="prerequisites" id="prerequisites" rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Masukkan prasyarat...">{{ old('prerequisites') }}</textarea>
                                @error('prerequisites')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Duration -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="duration_weeks" class="block text-sm font-medium text-gray-700">Durasi (Minggu)</label>
                                    <input type="number" name="duration_weeks" id="duration_weeks" 
                                           value="{{ old('duration_weeks') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           min="1" placeholder="12">
                                    @error('duration_weeks')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="total_hours" class="block text-sm font-medium text-gray-700">Total Jam</label>
                                    <input type="number" name="total_hours" id="total_hours" 
                                           value="{{ old('total_hours') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           min="1" step="0.5" placeholder="24">
                                    @error('total_hours')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('admin.curriculums.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Kurikulum
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>