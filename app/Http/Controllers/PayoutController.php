<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PendingPayout;
use Auth;

class PayoutController extends Controller
{
    public function pending()
    {
    	$payout = PendingPayout::where('status', '0')->where('user_id', Auth::user()->id)->get();

    	return view('instructor.revenue.pending', compact('payout'));

    }
}
