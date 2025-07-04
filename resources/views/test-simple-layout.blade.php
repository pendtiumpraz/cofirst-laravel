<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Test Layout - Simple
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Layout Test</h3>
                    <p>This is a simple test to verify that the x-app-layout component renders properly with:</p>
                    <ul class="list-disc ml-6 mt-4 space-y-2">
                        <li>HTML DOCTYPE and proper HTML structure</li>
                        <li>CSS and JavaScript assets loaded in head</li>
                        <li>Sidebar navigation visible</li>
                        <li>Top navbar with profile dropdown</li>
                        <li>Main content area properly styled</li>
                    </ul>
                    
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-blue-800">
                            <strong>Success:</strong> If you can see this styled content with the sidebar and navbar, 
                            the layout component is working correctly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 