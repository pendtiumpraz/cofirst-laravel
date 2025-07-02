<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Teacher CSS Test
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-red-500 text-white p-6 rounded-lg mb-6">
                <h1 class="text-2xl font-bold">🧪 Teacher CSS Test</h1>
                <p class="text-red-100">Jika background ini MERAH, maka Tailwind CSS sudah berfungsi!</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-500 text-white p-6 rounded-lg">
                    <h2 class="text-xl font-semibold">Blue Card</h2>
                    <p>Class: bg-blue-500</p>
                </div>
                
                <div class="bg-green-500 text-white p-6 rounded-lg">
                    <h2 class="text-xl font-semibold">Green Card</h2>
                    <p>Class: bg-green-500</p>
                </div>
                
                <div class="bg-yellow-500 text-white p-6 rounded-lg">
                    <h2 class="text-xl font-semibold">Yellow Card</h2>
                    <p>Class: bg-yellow-500</p>
                </div>
            </div>

            <div class="mt-6 bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Debug Info:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>Layout: x-app-layout</li>
                    <li>Route: {{ Route::currentRouteName() }}</li>
                    <li>User: {{ Auth::user()->name ?? 'Guest' }}</li>
                    <li>Role: {{ Auth::user()->roles->pluck('name')->implode(', ') ?? 'No Role' }}</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout> 