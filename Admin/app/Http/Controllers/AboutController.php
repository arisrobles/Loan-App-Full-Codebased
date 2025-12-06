<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Display system information and about page
     */
    public function index()
    {
        return view('about');
    }
}

