<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Debug Layout Component') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Testing Layout Component</h1>
                    <p class="text-gray-600">This is a test to see if the layout component is working correctly with CSS and styling.</p>
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