<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
	/**
	 * Display the list of payments
	 */
	public function index()
	{
		return view('dashboard.finance.payments');
	}

	/**
	 * Show distributions
	 */
	public function showDistributions(Request $request)
	{
		$data = [

		];
		return view('dashboard.finance.payment-distributions',$data)->withTitle('Payment Distributions');
	}
}
