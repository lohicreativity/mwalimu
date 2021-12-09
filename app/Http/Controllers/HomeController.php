<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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
        $data = [
           'staff'=>User::find(Auth::user()->id)->staff
        ];
    	return view('dashboard',$data)->withTitle('Dashboard');
    }
}
