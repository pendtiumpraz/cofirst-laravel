<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-blue-600 mb-8">CSS Test Page</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Card 1</h2>
                <p class="text-gray-600 mb-4">This is a test card with Tailwind CSS styling.</p>
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Test Button
                </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Card 2</h2>
                <p class="text-gray-600 mb-4">Another test card with different styling.</p>
                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Green Button
                </button>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Card 3</h2>
                <p class="text-gray-600 mb-4">Third test card with red button.</p>
                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Red Button
                </button>
            </div>
        </div>
        
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Responsive Test</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 bg-blue-100 p-4 rounded">
                    <h3 class="font-semibold text-blue-800">Responsive Column 1</h3>
                    <p class="text-blue-600">This should stack on mobile and be side-by-side on desktop.</p>
                </div>
                <div class="flex-1 bg-green-100 p-4 rounded">
                    <h3 class="font-semibold text-green-800">Responsive Column 2</h3>
                    <p class="text-green-600">Testing responsive behavior.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 