<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JavaScript Test</title>
    <script>
        console.log('🔍 Starting JavaScript debugging...');
        
        // Check if Vite assets are loading
        window.addEventListener('load', function() {
            console.log('📄 Page loaded');
            
            // Check for CSS
            const styles = document.querySelectorAll('link[rel="stylesheet"]');
            console.log('🎨 CSS files loaded:', styles.length);
            
            // Check for JS
            const scripts = document.querySelectorAll('script[src]');
            console.log('📜 External JS files:', scripts.length);
            
            scripts.forEach((script, index) => {
                console.log(`Script ${index + 1}:`, script.src);
            });
        });
        
        // Check for errors
        window.addEventListener('error', function(e) {
            console.error('❌ JavaScript Error:', e.error);
            console.error('File:', e.filename);
            console.error('Line:', e.lineno);
        });
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .debug-box {
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid;
        }
        .success { border-color: #10b981; background-color: #d1fae5; color: #065f46; }
        .error { border-color: #ef4444; background-color: #fee2e2; color: #991b1b; }
        .info { border-color: #3b82f6; background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f3f4f6;">
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="color: #1f2937; margin-bottom: 20px;">🔧 JavaScript Debug Test</h1>
        
        <div class="debug-box info">
            <strong>📊 Debug Info:</strong><br>
            <span id="debug-info">Loading...</span>
        </div>
        
        <div class="debug-box" id="css-test">
            <strong>🎨 CSS Test:</strong> Checking Tailwind CSS...
        </div>
        
        <button onclick="testInlineJS()" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px;">
            Test Inline JS
        </button>
        
        <button onclick="testAxios()" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px;">
            Test Axios
        </button>
        
        <div x-data="{ message: 'Alpine.js is working!', clicked: false }" class="debug-box" id="alpine-test">
            <strong>⚡ Alpine.js Test:</strong> 
            <span x-text="message"></span>
            <button @click="message = 'Alpine.js clicked!'; clicked = true" 
                    style="background: #8b5cf6; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Click me
            </button>
            <span x-show="clicked" style="color: #10b981; margin-left: 10px;">✅ Clicked!</span>
        </div>
        
        <div class="debug-box" id="result">
            <strong>📋 Results:</strong>
            <div id="test-results">No tests run yet...</div>
        </div>
    </div>

    <script>
        function updateDebugInfo() {
            const info = document.getElementById('debug-info');
            const now = new Date().toLocaleTimeString();
            const userAgent = navigator.userAgent.includes('Chrome') ? 'Chrome' : 
                             navigator.userAgent.includes('Firefox') ? 'Firefox' : 
                             navigator.userAgent.includes('Safari') ? 'Safari' : 'Unknown';
            
            info.innerHTML = `
                Time: ${now}<br>
                Browser: ${userAgent}<br>
                URL: ${window.location.href}<br>
                Scripts: ${document.querySelectorAll('script').length}<br>
                Stylesheets: ${document.querySelectorAll('link[rel="stylesheet"]').length}
            `;
        }
        
        function testInlineJS() {
            console.log('✅ Inline JavaScript is working!');
            addResult('✅ Inline JavaScript: Working');
        }
        
        function testAxios() {
            if (typeof axios !== 'undefined') {
                console.log('✅ Axios is loaded');
                addResult('✅ Axios: Loaded and available');
                
                // Test a simple request
                axios.get('/test-plain').then(response => {
                    addResult('✅ Axios HTTP request: Success');
                }).catch(error => {
                    addResult('❌ Axios HTTP request: Failed - ' + error.message);
                });
            } else {
                console.log('❌ Axios is not loaded');
                addResult('❌ Axios: Not loaded');
            }
        }
        
        function addResult(message) {
            const results = document.getElementById('test-results');
            results.innerHTML += '<div style="margin: 5px 0; padding: 5px; background: #f9fafb; border-radius: 4px;">' + message + '</div>';
        }
        
        // Run initial tests
        document.addEventListener('DOMContentLoaded', function() {
            updateDebugInfo();
            
            // Test CSS
            const cssTest = document.getElementById('css-test');
            const computedStyle = window.getComputedStyle(cssTest);
            if (computedStyle.backgroundColor === 'rgb(219, 234, 254)') {
                cssTest.className = 'debug-box success';
                addResult('✅ CSS: Tailwind classes applied correctly');
            } else {
                cssTest.className = 'debug-box error';
                addResult('❌ CSS: Tailwind classes not applied');
            }
            
            // Test Alpine.js
            setTimeout(() => {
                const alpineTest = document.getElementById('alpine-test');
                const alpineSpan = alpineTest.querySelector('[x-text]');
                if (alpineSpan && alpineSpan.textContent.includes('Alpine.js is working!')) {
                    addResult('✅ Alpine.js: Initialized and working');
                } else {
                    addResult('❌ Alpine.js: Not working properly');
                }
            }, 1000);
        });
        
        // Update debug info every 5 seconds
        setInterval(updateDebugInfo, 5000);
    </script>
</body>
</html> 