<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Student Test
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-4xl font-bold text-blue-600 mb-6">Student Dashboard Test</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-red-500 text-white p-6 rounded-lg">
                            <h3 class="text-xl font-bold">Red Card</h3>
                            <p>This should be RED background</p>
                        </div>
                        <div class="bg-green-500 text-white p-6 rounded-lg">
                            <h3 class="text-xl font-bold">Green Card</h3>
                            <p>This should be GREEN background</p>
                        </div>
                        <div class="bg-blue-500 text-white p-6 rounded-lg">
                            <h3 class="text-xl font-bold">Blue Card</h3>
                            <p>This should be BLUE background</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <button class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg">
                            Purple Button
                        </button>
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg ml-4">
                            Yellow Button
                        </button>
                    </div>
                    
                    <div class="mt-8 p-4 bg-gray-100 rounded-lg">
                        <p class="text-gray-800">If you can see colors and styling, CSS is working!</p>
                        <p class="text-sm text-gray-600 mt-2">Layout: x-app-layout</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 