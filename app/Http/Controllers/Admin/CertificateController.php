<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\ClassName;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Show bulk certificate generation form.
     */
    public function bulkGenerate()
    {
        $templates = CertificateTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $classes = ClassName::where('is_active', true)
            ->whereIn('status', ['active', 'completed'])
            ->with('course', 'teacher')
            ->orderBy('name')
            ->get();
            
        $students = User::role('student')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.certificates.bulk-generate', compact('templates', 'classes', 'students'));
    }

    /**
     * Process bulk certificate generation.
     */
    public function processBulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'class_id' => 'nullable|exists:class_names,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
            'all_students' => 'boolean',
            'issue_date' => 'required|date',
            'grade' => 'nullable|string|max:10',
            'score' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $template = CertificateTemplate::findOrFail($validated['template_id']);
        $generatedCount = 0;
        $errors = [];
        
        // Determine which students to generate certificates for
        $students = collect();
        
        if ($request->has('all_students') && $request->all_students && $request->has('class_id')) {
            // Get all students from the selected class
            $students = User::role('student')
                ->whereHas('enrollments', function($query) use ($request) {
                    $query->where('class_id', $request->class_id)
                          ->where('status', 'active');
                })
                ->get();
        } elseif ($request->has('student_ids') && !empty($request->student_ids)) {
            // Get selected students
            $students = User::whereIn('id', $request->student_ids)->get();
        }
        
        if ($students->isEmpty()) {
            return back()->with('error', 'No students selected for certificate generation.');
        }
        
        // Generate certificates for each student
        foreach ($students as $student) {
            try {
                // Get enrollment data if class is selected
                $enrollment = null;
                $className = null;
                $courseName = null;
                $teacherName = null;
                
                if ($request->has('class_id')) {
                    $enrollment = Enrollment::where('student_id', $student->id)
                        ->where('class_id', $request->class_id)
                        ->where('status', 'active')
                        ->first();
                        
                    if ($enrollment) {
                        $className = $enrollment->className;
                        $courseName = $className->course->name ?? '';
                        $teacherName = $className->teacher->name ?? '';
                    }
                }
                
                // Check if certificate already exists
                $existingCert = Certificate::where('student_id', $student->id)
                    ->where('template_id', $template->id)
                    ->when($request->has('class_id'), function($query) use ($request) {
                        $query->where('class_id', $request->class_id);
                    })
                    ->first();
                    
                if ($existingCert) {
                    $errors[] = "Certificate already exists for {$student->name}";
                    continue;
                }
                
                // Generate certificate
                $certificate = Certificate::create([
                    'student_id' => $student->id,
                    'template_id' => $template->id,
                    'class_id' => $request->class_id,
                    'certificate_number' => Certificate::generateCertificateNumber(),
                    'issue_date' => $validated['issue_date'],
                    'verification_code' => Str::random(32),
                    'metadata' => [
                        'student_name' => $student->name,
                        'course_name' => $courseName ?? 'General Course',
                        'class_name' => $className ? $className->name : 'General Class',
                        'teacher_name' => $teacherName ?? 'Instructor',
                        'grade' => $validated['grade'] ?? 'Pass',
                        'score' => $validated['score'] ?? null,
                        'duration' => $className ? $className->duration : '',
                        'type' => $template->type,
                    ],
                    'is_valid' => true,
                ]);
                
                $generatedCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Failed to generate certificate for {$student->name}: " . $e->getMessage();
            }
        }
        
        // Prepare feedback message
        $message = "Successfully generated {$generatedCount} certificate(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " and " . (count($errors) - 3) . " more...";
            }
        }
        
        return redirect()->route('admin.certificates.index')
            ->with($generatedCount > 0 ? 'success' : 'error', $message);
    }
}
