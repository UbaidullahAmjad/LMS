<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;
use App\User;
use Validator;

class ChangePasswordController extends Controller
{
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 200);
            // return $validator->errors()->first(); 
        }
        if(User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]))
        {
            return response()->json([
                'success'                   =>  true,
                'message'                   =>  'Password changed successfully'
            ], 200);
        }
        else
        {
            return response()->json([
                'error'                     =>  true,
                'message'                   =>  'Password cannot be changed'
            ], 200);
        }
   
    }
}
