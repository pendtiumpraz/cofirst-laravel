<!DOCTYPE html>
<html>
<head>
    <title>CSS Test</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-8">
            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">Test Page</div>
            <h1 class="block mt-1 text-lg leading-tight font-medium text-black">CSS Loading Test</h1>
            <p class="mt-2 text-gray-500">If you can see this styled properly with colors and spacing, CSS is working!</p>
            <button class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">Test Button</button>
            
            <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <strong>Success:</strong> Tailwind CSS is loaded and working properly!
            </div>
        </div>
    </div>
</body>
</html> 