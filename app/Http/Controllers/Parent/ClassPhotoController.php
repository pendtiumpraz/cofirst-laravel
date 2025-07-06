<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassPhotoController extends Controller
{
    /**
     * Display a listing of class photos.
     */
    public function index()
    {
        return view('parent.class-photos.index')->with([
            'message' => 'Class Photos viewing feature is coming soon!',
            'photos' => collect()
        ]);
    }

    /**
     * Show the specified class photo.
     */
    public function show(string $id)
    {
        return view('parent.class-photos.show')->with([
            'message' => 'Class Photo viewing feature is coming soon!',
            'photo' => null
        ]);
    }
}
