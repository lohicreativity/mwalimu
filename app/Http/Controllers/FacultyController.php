<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Campus;


class FacultyController extends Controller
{
    /**
     * Display a list of faculties
     */

    public function index()
    {
        $data = [
            'campuses' => Campus::all()
        ];

    	return view('dashboard.settings.faculties', $data)->withTitle('faculties');
    }

}
