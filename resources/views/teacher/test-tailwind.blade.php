@extends('layouts.app')

@section('title', 'Teacher Tailwind Test')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Test Header -->
        <div class="bg-red-500 text-white p-6 rounded-lg mb-6">
            <h1 class="text-2xl font-bold">🧪 Teacher Tailwind CSS Test</h1>
            <p class="text-red-100">Jika background ini merah, Tailwind sudah berfungsi!</p>
        </div>

        <!-- Test Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-500 text-white p-6 rounded-lg">
                <h2 class="text-xl font-semibold">Blue Card</h2>
                <p>Class: bg-blue-500</p>
            </div>
            
            <div class="bg-green-500 text-white p-6 rounded-lg">
                <h2 class="text-xl font-semibold">Green Card</h2>
                <p>Class: bg-green-500</p>
            </div>
            
            <div class="bg-purple-500 text-white p-6 rounded-lg">
                <h2 class="text-xl font-semibold">Purple Card</h2>
                <p>Class: bg-purple-500</p>
            </div>
        </div>

        <!-- Test Buttons -->
        <div class="mt-8 space-x-4">
            <button class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                Yellow Button
            </button>
            <button class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded">
                Indigo Button
            </button>
        </div>

        <!-- Debug Info -->
        <div class="mt-8 bg-gray-100 p-4 rounded">
            <h3 class="font-bold text-gray-800">Debug Info:</h3>
            <p class="text-sm text-gray-600">Route: {{ request()->url() }}</p>
            <p class="text-sm text-gray-600">View: teacher/test-tailwind.blade.php</p>
            <p class="text-sm text-gray-600">Layout: @extends('layouts.app')</p>
        </div>
    </div>
</div>
@endsection 