<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use App\User;
use Auth;
use Mail;
use Validator;
class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // return view('changePassword');
        $validator = Validator::make($request->all(), [
            'email' => 'required',
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
			$checkUser    =    User::where('email', $request->email)->first();
        if (!empty($checkUser)) {
            $verificationCode   =   rand(1000, 10000);

            $details = [
                'title' => 'Forgot Password',
                'body' => '<a href="/resetpasword">Reset Link</a>',
                'id'    => $checkUser->id,
            ];
            \Mail::to($request->email)->send(new \App\Mail\ForgetPasswordMail($details));
            //if(\Mail::to($request->email)->send(new \App\Mail\ForgetPasswordMail($details)))
            //{

            return response()->json([
                'success'             =>  true,
                'message'           =>  'Reset Link sent successfully'
            ], 200);
            // } else {
            //     return response()->json([
            //         'error'             =>  true,
            //         'message'           =>  'Something went wrong'
            //     ], 200);
            // }
            //				}
            //				else
            //				{
            //					return response()->json([
            //						'error'             =>  true,
            //						'message'           =>  'Something went wrong 1'
            //					], 200);                    
            //				}
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Email does not exist in our records'
            ], 200);
        }

        }
    } 
	
	
	public function getResetPasswordView($id)
    {
        return view('resetpasswordview', compact('id'));
    }

    public function storePassword(Request $request)
    {
        // dd($request->all());

        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('getresetpasswordview', $user->id)->with('message', 'Password Updated Successfully');
    }
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function store(Request $request)
    {
		$validator = Validator::make($request->all(), [
            'verification_code' => 'required',
			'new_password' => 'required',
			'new_confirm_password' => 'required',
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
            if(User::where('verification_code',$request->verification_code)->update(['password'=> Hash::make($request->new_password)]))
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
