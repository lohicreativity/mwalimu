<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class FacultyController extends Controller
{
    /**
     * Display a list of faculties
     */

    public function index()
    {
    	return view('dashboard.settings.faculties')->withTitle('faculties');
    }

}
