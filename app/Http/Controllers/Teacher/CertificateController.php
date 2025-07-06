<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Show certificate generation form.
     */
    public function generate()
    {
        return view('teacher.certificates.generate')->with([
            'message' => 'Certificate generation feature is coming soon!',
            'students' => collect()
        ]);
    }

    /**
     * Process certificate generation.
     */
    public function processGenerate(Request $request)
    {
        return redirect()->route('teacher.certificates.generate')
            ->with('info', 'Certificate generation feature is coming soon!');
    }
}
