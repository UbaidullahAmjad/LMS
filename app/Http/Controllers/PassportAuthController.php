<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;

class PassportAuthController extends Controller
{

    // public function signup(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string',
    //         'email' => 'required|string|email|unique:users',
    //         'password' => 'required|string|confirmed'
    //     ]);
    //     $user = new User([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password)
    //     ]);
    //     if($user->save())
    //     return response()->json([
    //         'message' => 'Successfully created user!'
    //     ], 201);
    // }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // $user = User::where('email',$request->email)->where('role_id',3)->first();
        // return response()->json([
        //     'error'             =>  true,
        //     'message'           =>  $request->password
        // ], 200);

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role_id'   =>  3])){

            $user = Auth::user();
            $token =  $user->createToken('MyApp')->accessToken;
            $name =  $user->first_name.' '.$user->last_name;
            $image =  $user->image;
            $phone =  $user->phone;
            $email =  $user->email;
			$user_id = $user->id;

            // return $this->sendResponse($success, 'User login successfully.');
            return response()->json([
                'token'         => $token,
                'name'          =>  $name,
                'image'          =>  url('/').'/public/user_images/'.$image,
                'phone'          =>  $phone,
                'email'          =>  $email,
				'user_id'		=> $user_id
                // 'expires_at' => Carbon::parse(
                //     $tokenResult->token->expires_at
                // )->toDateTimeString()
            ]);
        }
        else{
            // return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Incorrect email '
                ], 200);
        }
                // $credentials = request(['email', 'password']);
                // if(!Auth::attempt($credentials))
                // {
                //     return response()->json([
                //         'error'             =>  true,
                //         'message'           =>  'Incorrect email address or password'
                //     ], 200);
                // }
                // else
                // {
                //     $success['token'] =  $user->createToken('MyApp')-> accessToken;
                //     $success['name'] =  $user->name;

                //     return $this->sendResponse($success, 'User login successfully.');
                //     $user = $request->user();
                //     // $tokenResult = $user->createToken('Personal Access Token');
                //     $token = $user->token;
                //     // if ($request->remember_me)
                //     //     $token->expires_at = Carbon::now()->addWeeks(1);
                //     // if($token->save())
                //     // {
                //         return response()->json([
                //             'access_token'  => $user->access_token_id,
                //             'token_type'    => 'Bearer',
                //             'data'          =>  $user
                //             // 'expires_at' => Carbon::parse(
                //             //     $tokenResult->token->expires_at
                //             // )->toDateTimeString()
                //         ]);
                //     // }
                //     // else
                //     // {

                //     // }
                // }
            // return response()->json([
            //     'message' => 'Unauthorized'
            // ], 401);
    }

    public function logout(Request $request)
    {
        if(Auth::guard('api')->check())
        {
            // $request->user()->AauthAcessToken()->delete();
            // $request->user()->token()->revoke();
            if(Auth::guard('api')->user()->token()->revoke())
            {
                return response()->json([
                    'success'   =>  true,
                    'message'   => 'Successfully logged out'
                ]);
            }
            else
            {
                return response()->json([
                    'error'   =>  true,
                    'message'   => 'Something went wrong'
                ]);
            }
        }
        else
        {
            return response()->json([
                'error'     =>  true,
                'message'   => 'You need to login first'
            ]);
        }
    }


    public function userDetails(Request $request)
    {
        // if(Auth::check())
        if (Auth::guard('api')->check())
        {
            $getUser = User::where('id', Auth::guard('api')->user()->id)->where('role_id',3)->first();
            if(!empty($getUser))
            {
                $getUser['image']   =   url('/').'/public/user_images/'.$getUser['image'];
                return response()->json([
                    'success'               =>  true,
                    'data'                  =>  $getUser
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
        else
        {
            return response()->json([
                'error'     =>  true,
                'message'   => 'You need to login first'
            ]);
        }
        // return response()->json($request->user());
    }
}
