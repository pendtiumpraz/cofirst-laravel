<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('test');
    }
    
    public function blade()
    {
        $data = [
            'title' => 'Blade Test',
            'message' => 'This is a test message',
            'items' => ['Item 1', 'Item 2', 'Item 3']
        ];
        
        return view('test-blade', compact('data'));
    }
    
    public function plain()
    {
        return '<html><body><h1 style="color: red;">Plain HTML Test</h1><p>If you see this, PHP is working but Blade might not be.</p></body></html>';
    }
}
