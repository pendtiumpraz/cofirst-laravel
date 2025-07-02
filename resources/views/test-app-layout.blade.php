<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Test App Layout
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold text-blue-600 mb-4">Test App Layout</h1>
                    <p class="text-gray-700 mb-4">This is a test to see if x-app-layout component works properly.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-red-100 p-4 rounded-lg">
                            <h3 class="font-semibold text-red-800">Red Card</h3>
                            <p class="text-red-600">This should be red if CSS works</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-800">Green Card</h3>
                            <p class="text-green-600">This should be green if CSS works</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800">Blue Card</h3>
                            <p class="text-blue-600">This should be blue if CSS works</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Purple Button
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 