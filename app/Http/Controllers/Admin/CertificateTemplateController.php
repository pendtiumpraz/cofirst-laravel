<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CertificateTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.certificate-templates.index')->with([
            'message' => 'Certificate Templates management feature is coming soon!',
            'templates' => collect() // Empty collection for now
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.certificate-templates.create')->with([
            'message' => 'Certificate Template creation feature is coming soon!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.certificate-templates.index')
            ->with('info', 'Certificate Template creation feature is coming soon!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('admin.certificate-templates.show')->with([
            'message' => 'Certificate Template viewing feature is coming soon!',
            'template' => null
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.certificate-templates.edit')->with([
            'message' => 'Certificate Template editing feature is coming soon!',
            'template' => null
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.certificate-templates.index')
            ->with('info', 'Certificate Template editing feature is coming soon!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('admin.certificate-templates.index')
            ->with('info', 'Certificate Template deletion feature is coming soon!');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(string $id)
    {
        return redirect()->route('admin.certificate-templates.index')
            ->with('info', 'Certificate Template status toggle feature is coming soon!');
    }
}
