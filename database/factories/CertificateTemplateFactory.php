<?php

namespace Database\Factories;

use App\Models\CertificateTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificateTemplate>
 */
class CertificateTemplateFactory extends Factory
{
    protected $model = CertificateTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Template '.ucfirst($this->faker->unique()->word()),
            'type' => 'completion',
            'background_image' => null,
            'layout_config' => ['title' => ['x' => 50, 'y' => 20], 'student_name' => ['x' => 50, 'y' => 45]],
            'content_template' => 'Diberikan kepada {{student_name}} atas penyelesaian kursus {{course_name}}.',
            'available_variables' => ['student_name', 'course_name', 'class_name', 'issue_date', 'certificate_number'],
            'orientation' => 'landscape',
            'size' => 'A4',
            'is_active' => true,
        ];
    }
}
