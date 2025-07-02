<!DOCTYPE html>
<html>
<head>
    <title>Blade Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Blade Template Test</h1>
        
        @if(isset($message))
            <div class="success">✅ Blade Directives Working: {{ $message }}</div>
        @else
            <div class="error">❌ Blade Variables Not Working</div>
        @endif
        
        <h2>Basic Tests:</h2>
        <ul>
            <li>Current Time: {{ now()->format('Y-m-d H:i:s') }}</li>
            <li>PHP Version: {{ phpversion() }}</li>
            <li>Laravel Version: {{ app()->version() }}</li>
        </ul>
        
        <h2>Loop Test:</h2>
        @for($i = 1; $i <= 3; $i++)
            <p>Loop iteration: {{ $i }}</p>
        @endfor
        
        <h2>Conditional Test:</h2>
        @if(true)
            <p class="success">✅ If condition working</p>
        @else
            <p class="error">❌ If condition not working</p>
        @endif
        
        <p><a href="/test-plain">Test Plain HTML</a> | <a href="/teacher/classes">Back to Teacher Classes</a></p>
    </div>
</body>
</html> 