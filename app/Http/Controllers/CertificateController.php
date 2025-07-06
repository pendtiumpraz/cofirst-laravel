<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\User;
use App\Models\Course;
use App\Models\ClassName;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Display a listing of certificates.
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['student', 'course', 'class', 'template']);

        // Filter by role
        $user = Auth::user();
        if ($user->hasRole('student')) {
            $query->where('student_id', $user->id);
        } elseif ($user->hasRole('teacher')) {
            $query->where('issued_by', $user->id);
        } elseif ($user->hasRole('parent')) {
            $childrenIds = $user->children->pluck('id');
            $query->whereIn('student_id', $childrenIds);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by validity
        if ($request->has('is_valid')) {
            $query->where('is_valid', $request->boolean('is_valid'));
        }

        $certificates = $query->orderBy('issue_date', 'desc')->paginate(20);

        return view('certificates.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new certificate.
     */
    public function create()
    {
        $templates = CertificateTemplate::where('is_active', true)->get();
        $students = User::role('student')->where('is_active', true)->get();
        $courses = Course::where('is_active', true)->get();
        $classes = ClassName::where('is_active', true)->get();

        return view('certificates.create', compact('templates', 'students', 'courses', 'classes'));
    }

    /**
     * Store a newly created certificate.
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'student_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'class_id' => 'nullable|exists:class_names,id',
            'type' => 'required|in:completion,achievement,participation',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'metadata' => 'nullable|array',
        ]);

        $certificateData = $request->only([
            'template_id', 'student_id', 'course_id', 'class_id',
            'type', 'title', 'description', 'issue_date', 'expiry_date', 'metadata'
        ]);
        $certificateData['issued_by'] = Auth::id();

        // Create certificate record
        $certificate = Certificate::create($certificateData);

        // Generate PDF and QR code
        $this->certificateService->generateCertificate($certificate);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', 'Certificate created successfully!');
    }

    /**
     * Display the specified certificate.
     */
    public function show(Certificate $certificate)
    {
        // Check access permissions
        $user = Auth::user();
        if (!$this->canViewCertificate($user, $certificate)) {
            abort(403, 'Unauthorized access to certificate.');
        }

        $certificate->load(['student', 'course', 'class', 'template', 'issuer']);

        return view('certificates.show', compact('certificate'));
    }

    /**
     * Download certificate PDF
     */
    public function download(Certificate $certificate)
    {
        // Check access permissions
        $user = Auth::user();
        if (!$this->canViewCertificate($user, $certificate)) {
            abort(403, 'Unauthorized access to certificate.');
        }

        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            // Regenerate if missing
            $this->certificateService->generateCertificate($certificate);
        }

        return Storage::disk('public')->download($certificate->file_path, $certificate->certificate_number . '.pdf');
    }

    /**
     * Verify certificate by code (public access)
     */
    public function verify($code)
    {
        $certificate = Certificate::where('verification_code', $code)
            ->with(['student', 'course', 'class', 'issuer'])
            ->first();

        if (!$certificate) {
            return view('certificates.verify', ['certificate' => null, 'error' => 'Certificate not found']);
        }

        $isValid = $certificate->is_valid && !$certificate->isExpired();

        return view('certificates.verify', compact('certificate', 'isValid'));
    }

    /**
     * Invalidate a certificate
     */
    public function invalidate(Certificate $certificate)
    {
        // Only admins can invalidate
        if (!Auth::user()->hasRole(['admin', 'superadmin'])) {
            abort(403, 'Unauthorized action.');
        }

        $certificate->invalidate();

        return redirect()->route('certificates.index')
            ->with('success', 'Certificate invalidated successfully.');
    }

    /**
     * Bulk generate certificates for a class
     */
    public function bulkCreate()
    {
        $templates = CertificateTemplate::where('is_active', true)->get();
        $classes = ClassName::where('status', 'completed')->with('enrollments.student')->get();

        return view('certificates.bulk-create', compact('templates', 'classes'));
    }

    /**
     * Process bulk certificate generation
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'class_id' => 'required|exists:class_names,id',
            'type' => 'required|in:completion,achievement,participation',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'issue_date' => 'required|date',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $class = ClassName::with('course')->find($request->class_id);
        $template = CertificateTemplate::find($request->template_id);

        $created = 0;
        foreach ($request->student_ids as $studentId) {
            // Check if certificate already exists
            $exists = Certificate::where('student_id', $studentId)
                ->where('class_id', $class->id)
                ->where('type', $request->type)
                ->exists();

            if (!$exists) {
                $certificate = Certificate::create([
                    'template_id' => $template->id,
                    'student_id' => $studentId,
                    'course_id' => $class->course_id,
                    'class_id' => $class->id,
                    'type' => $request->type,
                    'title' => $request->title,
                    'description' => $request->description,
                    'issue_date' => $request->issue_date,
                    'issued_by' => Auth::id(),
                ]);

                $this->certificateService->generateCertificate($certificate);
                $created++;
            }
        }

        return redirect()->route('certificates.index')
            ->with('success', "Successfully created {$created} certificates.");
    }

    /**
     * Check if user can view certificate
     */
    private function canViewCertificate($user, $certificate)
    {
        // Admin can view all
        if ($user->hasRole(['admin', 'superadmin'])) {
            return true;
        }

        // Student can view own
        if ($user->hasRole('student') && $certificate->student_id === $user->id) {
            return true;
        }

        // Teacher can view certificates they issued
        if ($user->hasRole('teacher') && $certificate->issued_by === $user->id) {
            return true;
        }

        // Parent can view children's certificates
        if ($user->hasRole('parent') && $user->children->pluck('id')->contains($certificate->student_id)) {
            return true;
        }

        return false;
    }
}