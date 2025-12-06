<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Display help and support page
     */
    public function index()
    {
        return view('help');
    }
}

