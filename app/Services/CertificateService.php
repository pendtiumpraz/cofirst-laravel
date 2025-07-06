<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class CertificateService
{
    /**
     * Generate certificate PDF and QR code
     */
    public function generateCertificate(Certificate $certificate)
    {
        // Load relationships
        $certificate->load(['student', 'course', 'class', 'template', 'issuer']);

        // Generate QR code
        $qrCodePath = $this->generateQrCode($certificate);
        $certificate->update(['qr_code' => $qrCodePath]);

        // Generate PDF
        $pdfPath = $this->generatePdf($certificate);
        $certificate->update(['file_path' => $pdfPath]);

        return $certificate;
    }

    /**
     * Generate QR code for certificate verification
     */
    protected function generateQrCode(Certificate $certificate)
    {
        $qrCodeContent = route('certificate.verify', $certificate->verification_code);
        
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrCodeContent);

        $filename = 'certificates/qrcodes/' . $certificate->certificate_number . '.png';
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Generate PDF from certificate template
     */
    protected function generatePdf(Certificate $certificate)
    {
        $template = $certificate->template;
        
        // Prepare data for template
        $data = $this->prepareTemplateData($certificate);

        // Determine which view to use based on template type
        $viewName = 'certificates.templates.' . $template->type;
        if (!View::exists($viewName)) {
            $viewName = 'certificates.templates.default';
        }

        // Generate PDF
        $pdf = PDF::loadView($viewName, $data);
        
        // Set PDF options
        $pdf->setPaper($template->size, $template->orientation);
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        // Save PDF
        $filename = 'certificates/pdfs/' . $certificate->certificate_number . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Prepare data for certificate template
     */
    protected function prepareTemplateData(Certificate $certificate)
    {
        $student = $certificate->student;
        $course = $certificate->course;
        $class = $certificate->class;
        $issuer = $certificate->issuer;

        $data = [
            'certificate' => $certificate,
            'student_name' => $student->name,
            'course_name' => $course ? $course->name : '',
            'class_name' => $class ? $class->name : '',
            'issue_date' => $certificate->issue_date->format('d F Y'),
            'certificate_number' => $certificate->certificate_number,
            'verification_code' => $certificate->verification_code,
            'qr_code_url' => $certificate->qr_code_url,
            'verification_url' => $certificate->verification_url,
            'teacher_name' => $issuer->name,
            'company_name' => 'CoFirst',
            'company_tagline' => 'Learn Coding with Japanese Quality',
            'template' => $certificate->template,
        ];

        // Add metadata if available
        if ($certificate->metadata) {
            $data = array_merge($data, $certificate->metadata);
        }

        // Add custom template variables
        if ($certificate->template->available_variables) {
            foreach ($certificate->template->available_variables as $variable) {
                if (!isset($data[$variable])) {
                    $data[$variable] = '';
                }
            }
        }

        return $data;
    }

    /**
     * Regenerate certificate files
     */
    public function regenerateCertificate(Certificate $certificate)
    {
        // Delete old files
        if ($certificate->qr_code) {
            Storage::disk('public')->delete($certificate->qr_code);
        }
        if ($certificate->file_path) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        // Generate new files
        return $this->generateCertificate($certificate);
    }
}