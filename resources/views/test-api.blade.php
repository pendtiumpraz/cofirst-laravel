<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .results {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        pre {
            background: #fff;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <h1>API Test Page</h1>
    <p>Testing API endpoints directly in browser</p>
    
    <div id="results" class="results"></div>
    
    <script>
        // Test authentication first
        fetch('/test-schedule-api', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Auth test response:', data);
            if (data.error) {
                document.getElementById('results').innerHTML += `
                    <h3 class="error">✗ Authentication Error:</h3>
                    <pre class="error">${data.error}</pre>
                `;
            } else {
                document.getElementById('results').innerHTML += `
                    <h3 class="success">✓ Authentication Working:</h3>
                    <pre>User: ${data.user}<br>Class ID: ${data.class_id}<br>Class Name: ${data.class_name}</pre>
                `;
                // Use the class ID from the auth test
                testScheduleEndpoints(data.class_id);
            }
        })
        .catch(error => {
            console.error('Auth test error:', error);
            document.getElementById('results').innerHTML += `
                <h3 class="error">✗ Authentication Test Error:</h3>
                <pre class="error">${error.message}</pre>
            `;
        });
        
        function testScheduleEndpoints(classId) {
            console.log('Testing schedule endpoints with class ID:', classId);
        
            // Test Teachers API
            fetch(`/api/schedule-data/teachers/${classId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Teachers API Status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            console.log('Teachers API Response:', data);
            document.getElementById('results').innerHTML += `
                <h3 class="success">✓ Teachers API Response (Status: 200):</h3>
                <pre>${data}</pre>
            `;
        })
        .catch(error => {
            console.error('Teachers API Error:', error);
            document.getElementById('results').innerHTML += `
                <h3 class="error">✗ Teachers API Error:</h3>
                <pre class="error">${error.message}</pre>
            `;
        });
        
        // Test Students API
        fetch(`/api/schedule-data/students/${classId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Students API Status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            console.log('Students API Response:', data);
            document.getElementById('results').innerHTML += `
                <h3 class="success">✓ Students API Response (Status: 200):</h3>
                <pre>${data}</pre>
            `;
        })
        .catch(error => {
            console.error('Students API Error:', error);
            document.getElementById('results').innerHTML += `
                <h3 class="error">✗ Students API Error:</h3>
                <pre class="error">${error.message}</pre>
            `;
        });
        
        // Test with invalid class ID
        fetch(`/api/schedule-data/teachers/999999`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Invalid Class Test Status:', response.status);
            return response.text();
        })
        .then(data => {
            console.log('Invalid Class Test Response:', data);
            document.getElementById('results').innerHTML += `
                <h3>Invalid Class ID Test (should return empty array):</h3>
                <pre>${data}</pre>
            `;
        })
        .catch(error => {
            console.error('Invalid Class Test Error:', error);
            document.getElementById('results').innerHTML += `
                <h3 class="error">✗ Invalid Class Test Error:</h3>
                <pre class="error">${error.message}</pre>
            `;
        });
        }
    </script>
</body>
</html> 