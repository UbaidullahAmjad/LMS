<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Auth;
class TeacherController extends Controller
{
    // begin get top teachers
    public function topTeachers(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
            $getUser = User::where('role_id',2)->orderBy('id','desc')->limit(6)->get();
            if(!empty($getUser))
            {
                // $getUser['image']   =   url('/').'/public/user_images/'.$getUser['image'];
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
        // }
        // else
        // {
        //     return response()->json([
        //         'error'     =>  true,
        //         'message'   => 'You need to login first'
        //     ]);
        // }
    }
    // end get top teachers
}
