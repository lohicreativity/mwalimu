<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display login page
     */
    public function index()
    {
    	return view('auth.login')->withTitle('Login');
    }

    /**
     * Display login page
     */
    public function dashboard()
    {
    	return view('dashboard')->withTitle('Dashboard');
    }
}
