<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            DEBUG: Blade Simple Test
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Simple Blade Test</h1>
                    
                    <div class="mb-4">
                        <p class="text-gray-600">Current time: {{ now() }}</p>
                        <p class="text-gray-600">User: {{ auth()->user()->name }}</p>
                    </div>

                    @if(true)
                        <div class="mb-4">
                            <p class="text-green-600">✅ @if directive works</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Test Button
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 