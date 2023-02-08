<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Auth;
use TwilioMsg;


class TestController extends Controller
{
    
   	public function test()
   	{
   		return view('test');
   	}

   	
   

}
