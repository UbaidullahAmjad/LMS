<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Auth;
use Exception;
use App\User;

class SocialMediaController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {

            $user = Socialite::driver('google')->user();

            // $finduser = User::where('email', $user->email)->where('status','Active')->first();
            $finduser = User::where('email', $user->email)->first();


            if($finduser){

                if(Auth::login($finduser))
                // if(Auth::user()->role_id    ==  1)
                // {
                //     return redirect('/orders');
                // }
                // else
                // {
                    return redirect('/home');
                // }
            }else{
                $checkUser = User::where('email', $user->email)->first();
                if(!empty($checkUser))
                {
                    return redirect('/')->withErrors(['Your account is inactive']);
                }
                else
                {
                    return redirect('/')->withErrors(['Email does not exist in our records']);
                }

                // {
                //     return response()->json([
                //         'success'                               =>  true,
                //         'name'                                  =>  $name,
                //         'lists'                                 =>  $businessList
                //     ]);
                // }
                // else
                // {

                // }

                // {
                //     return response()->json([
                //         'success'           =>  true,
                //         'message'           =>  'Business updated successfully'
                //     ], 200);
                // } else {
                //     return response()->json([
                //         'error'             =>  true,
                //         'message'           =>  'business cannot be updated'
                //     ], 200);
                // }



                // $newUser = User::create([
                //     'name' => $user->name,
                //     'email' => $user->email,
                //     'google_id'=> $user->id,
                //     'password' => encrypt('12345678')
                // ]);

                // Auth::login($newUser);
                // dd('Else error');
                // return view('404')->with('error','Your account is not activated please contact with admin');
                // return redirect('/404');


                // return redirect('/')->withErrors(['Email does not exist in our records']);
                // return back()->with('error','Email does not exist in our records');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
