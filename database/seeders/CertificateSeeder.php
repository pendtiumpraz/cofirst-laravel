<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;

/**
 * Certificates for every completed demo enrollment (no PDF files; rows only).
 *
 * Numbers ("CF-DEMO-0001") and verification codes are deterministic so the
 * public verification page can be demoed; keyed by certificate_number.
 */
class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = CertificateTemplate::where('name', CertificateTemplateSeeder::DEFAULT_NAME)->first()
            ?? CertificateTemplate::orderBy('id')->first();

        if (! $template) {
            $this->command?->warn('Tidak ada template sertifikat; CertificateSeeder dilewati.');

            return;
        }

        $issuerId = DemoContext::user('admin')->id;
        $sequence = 0;

        $enrollments = Enrollment::with(['student', 'className.course', 'className.teachers'])
            ->whereIn('class_id', DemoContext::classIds())
            ->where('status', Enrollment::STATUS_COMPLETED)
            ->orderBy('class_id')->orderBy('student_id')->get();

        foreach ($enrollments as $enrollment) {
            $sequence++;
            $class = $enrollment->className;
            $score = 78 + DemoContext::pick('cert:'.$enrollment->student->email.$class->name, 20);
            $number = sprintf('CF-DEMO-%04d', $sequence);

            Certificate::updateOrCreate(['certificate_number' => $number], [
                'template_id' => $template->id,
                'student_id' => $enrollment->student_id,
                'course_id' => $class->course_id,
                'class_id' => $class->id,
                'type' => 'completion',
                'title' => 'Sertifikat Kelulusan '.$class->course->name,
                'description' => "Telah menyelesaikan kelas {$class->name}.",
                'issue_date' => $class->end_date->copy()->addDays(3)->toDateString(),
                'expiry_date' => null,
                'metadata' => [
                    'student_name' => $enrollment->student->name,
                    'course_name' => $class->course->name,
                    'class_name' => $class->name,
                    'teacher_name' => $class->teachers->first()?->name,
                    'score' => $score,
                    'grade' => $score >= 90 ? 'A' : ($score >= 80 ? 'B+' : 'B'),
                ],
                'qr_code' => null,
                'verification_code' => strtoupper(substr(hash('sha256', 'coding-first-demo-'.$number), 0, 12)),
                'file_path' => null,
                'is_valid' => true,
                'issued_by' => $issuerId,
            ]);
        }

        $this->command?->info("Demo certificates: {$sequence}.");
    }
}
