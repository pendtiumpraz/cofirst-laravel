<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate - {{ $certificate_number }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #ffffff;
        }
        
        .certificate-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-image: url('{{ public_path('images/certificate-bg-default.jpg') }}');
            background-size: cover;
            background-position: center;
        }
        
        .certificate-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 80%;
        }
        
        .logo {
            width: 150px;
            margin-bottom: 30px;
        }
        
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .subtitle {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 40px;
        }
        
        .recipient {
            font-size: 36px;
            font-weight: bold;
            color: #1a365d;
            margin: 30px 0;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .description {
            font-size: 18px;
            color: #4a5568;
            line-height: 1.6;
            margin: 30px 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .details {
            margin-top: 40px;
        }
        
        .detail-row {
            display: inline-block;
            margin: 0 30px;
            text-align: center;
        }
        
        .detail-label {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .detail-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            padding: 0 100px;
        }
        
        .signature {
            text-align: center;
        }
        
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #2d3748;
            margin-bottom: 10px;
        }
        
        .signature-name {
            font-size: 14px;
            color: #2d3748;
            font-weight: bold;
        }
        
        .signature-title {
            font-size: 12px;
            color: #718096;
        }
        
        .qr-section {
            position: absolute;
            bottom: 30px;
            right: 30px;
            text-align: center;
        }
        
        .qr-code {
            width: 100px;
            height: 100px;
        }
        
        .verification-code {
            font-size: 10px;
            color: #718096;
            margin-top: 5px;
        }
        
        .footer {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-content">
            @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            @endif
            
            <h1 class="title">Certificate of {{ ucfirst($certificate->type) }}</h1>
            
            <p class="subtitle">This is to certify that</p>
            
            <h2 class="recipient">{{ $student_name }}</h2>
            
            <p class="description">
                {{ $certificate->description ?: "has successfully completed the {$course_name} course at {$company_name} with dedication and excellence." }}
            </p>
            
            <div class="details">
                @if($course_name)
                <div class="detail-row">
                    <div class="detail-label">Course</div>
                    <div class="detail-value">{{ $course_name }}</div>
                </div>
                @endif
                
                @if($class_name)
                <div class="detail-row">
                    <div class="detail-label">Class</div>
                    <div class="detail-value">{{ $class_name }}</div>
                </div>
                @endif
                
                <div class="detail-row">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">{{ $issue_date }}</div>
                </div>
            </div>
            
            <div class="signatures">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $teacher_name }}</div>
                    <div class="signature-title">Instructor</div>
                </div>
                
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">Director</div>
                    <div class="signature-title">{{ $company_name }}</div>
                </div>
            </div>
        </div>
        
        <div class="qr-section">
            @if($certificate->qr_code)
            <img src="{{ public_path('storage/' . $certificate->qr_code) }}" alt="QR Code" class="qr-code">
            @endif
            <div class="verification-code">{{ $certificate_number }}</div>
        </div>
        
        <div class="footer">
            {{ $company_name }} - {{ $company_tagline }}
        </div>
    </div>
</body>
</html>