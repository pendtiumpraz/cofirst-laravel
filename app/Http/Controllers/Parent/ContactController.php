<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Show the contact admin form.
     */
    public function index()
    {
        return view('parent.contact-admin')->with([
            'message' => 'Contact Admin feature is coming soon!'
        ]);
    }

    /**
     * Send message to admin.
     */
    public function send(Request $request)
    {
        return redirect()->route('parent.contact-admin')
            ->with('info', 'Contact Admin feature is coming soon!');
    }
}
