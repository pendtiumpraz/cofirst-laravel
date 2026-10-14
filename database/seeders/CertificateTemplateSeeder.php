<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

/**
 * The default certificate template (production-safe: created only when the
 * table has no template yet, so a renamed or edited default is left alone).
 */
class CertificateTemplateSeeder extends Seeder
{
    /** Name of the default template. */
    public const DEFAULT_NAME = 'Template Standar Kelulusan';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reason: the default is only needed when there is no template at all; keying
        // on the name would re-create it after an admin renamed it.
        if (CertificateTemplate::query()->exists()) {
            return;
        }

        CertificateTemplate::create([
            'name' => self::DEFAULT_NAME,
            'type' => 'completion',
            'background_image' => null,
            'layout_config' => [
                'title' => ['x' => 50, 'y' => 18, 'font_size' => 40, 'align' => 'center'],
                'student_name' => ['x' => 50, 'y' => 42, 'font_size' => 32, 'align' => 'center'],
                'body' => ['x' => 50, 'y' => 58, 'font_size' => 16, 'align' => 'center'],
                'signature' => ['x' => 78, 'y' => 82, 'font_size' => 14, 'align' => 'center'],
                'qr_code' => ['x' => 12, 'y' => 80, 'size' => 90],
            ],
            'content_template' => 'Diberikan kepada {{student_name}} yang telah menyelesaikan kursus {{course_name}} '
                .'pada kelas {{class_name}} dengan predikat {{grade}}. Diterbitkan pada {{issue_date}} '
                .'dengan nomor {{certificate_number}}.',
            'available_variables' => [
                'student_name', 'course_name', 'class_name', 'issue_date', 'certificate_number',
                'teacher_name', 'grade', 'score', 'duration', 'company_name', 'company_tagline',
            ],
            'orientation' => 'landscape',
            'size' => 'A4',
            'is_active' => true,
        ]);
    }
}
