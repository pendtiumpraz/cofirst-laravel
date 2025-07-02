<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Component Test
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-4xl font-bold text-red-600 mb-6">Component Test</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-red-500 text-white p-6 rounded-lg">
                            <h3 class="text-xl font-bold">Red Card</h3>
                            <p>Should be RED background</p>
                        </div>
                        <div class="bg-green-500 text-white p-6 rounded-lg">
                            <h3 class="text-xl font-bold">Green Card</h3>
                            <p>Should be GREEN background</p>
                        </div>
                        <div class="bg-blue-500 text-white p-6 rounded-lg">
                            <h3 class="text-xl font-bold">Blue Card</h3>
                            <p>Should be BLUE background</p>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <p class="text-lg text-gray-700">If you see colors, the x-app-layout component is working!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 