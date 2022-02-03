<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\Payment;
use App\Models\User;
use Auth;

class PaymentController extends Controller
{
	/**
	 * Display the list of payments
	 */
	public function index(Request $request)
	{
		$data = [
            'payments'=>Payment::with(['usable'])->latest()->paginate(20),
            'staff'=>User::find(Auth::user()->id)->staff
		];
		return view('dashboard.finance.payments',$data)->withTitle('Payments');
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
