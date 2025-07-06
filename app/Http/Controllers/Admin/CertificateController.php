<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Show bulk certificate generation form.
     */
    public function bulkGenerate()
    {
        return view('admin.certificates.bulk-generate')->with([
            'message' => 'Bulk Certificate Generation feature is coming soon!',
            'classes' => collect(), // Empty collection for now
            'students' => collect() // Empty collection for now
        ]);
    }

    /**
     * Process bulk certificate generation.
     */
    public function processBulkGenerate(Request $request)
    {
        return redirect()->route('admin.certificates.bulk-generate')
            ->with('info', 'Bulk Certificate Generation feature is coming soon!');
    }
}
