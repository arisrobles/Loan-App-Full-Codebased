<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display legal menu or specific legal content
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'menu'); // menu, terms, privacy, agreement
        
        return view('legal', compact('type'));
    }
}

