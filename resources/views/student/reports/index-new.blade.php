<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Reports - New
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">TEST: My Academic Reports</h1>
                    
                    <div class="mb-4">
                        <p class="text-gray-600">Current time: {{ now() }}</p>
                        <p class="text-gray-600">Reports count: {{ $reports->count() }}</p>
                    </div>

                    @if($reports->count() > 0)
                        <div class="bg-green-100 p-4 rounded">
                            <p class="text-green-800">Found {{ $reports->count() }} reports</p>
                        </div>
                    @else
                        <div class="bg-yellow-100 p-4 rounded">
                            <p class="text-yellow-800">No reports found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 