<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;
use App\User;
use Auth;
use Validator;

class ChangePasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // public function index()
    // {
    //     return view('changePassword');
    // } 
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function store(Request $request)
    {
		
		$validator = Validator::make($request->all(), [
           	'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        if ($validator->fails()) 
		{
         
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Something went wrong'
            ], 200);    
        }
        else
        {
			$hashedPassword = Auth::user()->password;
            if(User::find(auth()->user()->id)->where('password',$hashedPassword)->update(['password'=> Hash::make($request->new_password)]))
            {
                return response()->json([
                    'success'               =>  true,
                    'message'                  =>  'Password change successfully.'
                ], 200);
            }
            else
            {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something went wrong'
                ], 200);
            }
        }
    }
}
