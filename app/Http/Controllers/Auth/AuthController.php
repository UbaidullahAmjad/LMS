<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use App\User;
use File;
use App\Mail\WelcomeUser;
use Illuminate\Support\Facades\Mail;
use App\Setting;

class AuthController extends Controller
{
   /**
     * Redirect the user to the OAuth Provider.
     *
     * @return Response
     */
    

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that
     * redirect them to the authenticated users homepage.
     *
     * @return Response
     */
    public function handleProviderCallback($provider, Request $request)
    {
        try{
            $user = Socialite::driver($provider)->user();
        }catch(\Exception $ex){
            if(!$request->has('code') || $request->has('denied')) {
                return redirect('/');
            }
            $user = Socialite::driver($provider)->stateless()->user();
        }

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        return redirect()->intended('/');
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  User
     */
    public function findOrCreateUser($user, $provider)
    {
        if($user->email == Null){
            $user->email = $user->id.'@facebook.com';
        }
        $authUser = User::where('email', $user->email)->first();
        $providerField = "{$provider}_id";
        if($authUser){
            if ($authUser->{$providerField} == $user->id) {
                $authUser->email_verified_at = \Carbon\Carbon::now()->toDateTimeString();
                $authUser->save();
                return $authUser;
            }
            else{
                $authUser->{$providerField} = $user->id;
                $authUser->email_verified_at = \Carbon\Carbon::now()->toDateTimeString();
                $authUser->save();
                return $authUser;
            }
        }

        if($user->avatar != NULL && $user->avatar != ""){
            $fileContents = @file_get_contents($user->getAvatar());
            $user_profile = File::put(public_path() . '/images/user_img/' . $user->getId() . ".jpg", $fileContents);
            $name = $user->getId() . ".jpg";
        }
        else {
            $name = NULL;
        }

        $verified = \Carbon\Carbon::now()->toDateTimeString();

        $setting = Setting::first();

        $auth_user = User::create([
            'fname'     => $user->name,
            'email'    => $user->email,
            'user_img'    => $name,
            'email_verified_at'  => $verified,
            $providerField => $user->id,
        ]);


        if($setting->w_email_enable == 1){
            try{
               
                Mail::to($auth_user['email'])->send(new WelcomeUser($auth_user));
               
            }
            catch(\Swift_TransportException $e){

            }
        }



        return $auth_user;



    }
}
