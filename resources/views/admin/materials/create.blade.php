<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tambah Materi Baru') }}
            </h2>
            <a href="{{ route('admin.materials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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

                    <form action="{{ route('admin.materials.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Material Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Judul Materi <span class="text-red-500">*</span></label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                       required placeholder="Masukkan judul materi">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Syllabus Selection -->
                            <div>
                                <label for="syllabus_id" class="block text-sm font-medium text-gray-700">Silabus <span class="text-red-500">*</span></label>
                                <select name="syllabus_id" id="syllabus_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                        required>
                                    <option value="">Pilih Silabus</option>
                                    @foreach($syllabuses as $syllabus)
                                        <option value="{{ $syllabus->id }}" {{ old('syllabus_id') == $syllabus->id ? 'selected' : '' }}>
                                            {{ $syllabus->curriculum->name }} - {{ $syllabus->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('syllabus_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Material Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Tipe Materi <span class="text-red-500">*</span></label>
                                <select name="type" id="type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                        required>
                                    <option value="">Pilih Tipe</option>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meeting Range -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="meeting_start" class="block text-sm font-medium text-gray-700">Pertemuan Mulai <span class="text-red-500">*</span></label>
                                    <input type="number" name="meeting_start" id="meeting_start" 
                                           value="{{ old('meeting_start', 1) }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           min="1" required placeholder="1">
                                    @error('meeting_start')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="meeting_end" class="block text-sm font-medium text-gray-700">Pertemuan Selesai <span class="text-red-500">*</span></label>
                                    <input type="number" name="meeting_end" id="meeting_end" 
                                           value="{{ old('meeting_end', 1) }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           min="1" required placeholder="1">
                                    @error('meeting_end')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea name="description" id="description" rows="4" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Masukkan deskripsi materi...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Content -->
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700">Konten</label>
                                <textarea name="content" id="content" rows="8" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Masukkan konten materi...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700">File Materi</label>
                                <input type="file" name="file" id="file" 
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mp3,.jpg,.jpeg,.png">
                                <p class="mt-1 text-sm text-gray-500">Upload file materi (PDF, DOC, PPT, Video, Audio, Gambar)</p>
                                @error('file')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div>
                                <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                                <input type="url" name="url" id="url" value="{{ old('url') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                       placeholder="https://example.com">
                                <p class="mt-1 text-sm text-gray-500">URL eksternal untuk materi (opsional)</p>
                                @error('url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Duration and Order -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="duration" class="block text-sm font-medium text-gray-700">Durasi (menit)</label>
                                    <input type="number" name="duration" id="duration" 
                                           value="{{ old('duration') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           min="1" placeholder="30">
                                    @error('duration')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="order" class="block text-sm font-medium text-gray-700">Urutan</label>
                                    <input type="number" name="order" id="order" 
                                           value="{{ old('order', 1) }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           min="1" placeholder="1">
                                    @error('order')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
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
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('admin.materials.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Materi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>