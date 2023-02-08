<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\State;
use App\Country;
use App\User;
use App\Role;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class ProfileController extends Controller
{
    // begin edit view
    public function edit()
    {
        $id     =   Auth::user()->id;
        $getUser    =   User::where('role_id', Auth::user()->role_id)->where('id',$id)->first();
        if(!empty($getUser->Country['id']))
        {
            $selectedCountryId  =  $getUser->Country['id'];
            $selectedCountryName  =  $getUser->Country['name'];
        }
        else
        {
            $selectedCountryId  =  '';
            $selectedCountryName  =  '';
        }
        if(!empty($getUser->State['id']))
        {
            $selectedStateId  =  $getUser->State['id'];
            $selectedStateName  =  $getUser->State['name'];
        }
        else
        {
            $selectedStateId  =  '';
            $selectedStateName  =  '';
        }
        if(!empty($getUser->City['id']))
        {
            $selectedCityId  =  $getUser->City['id'];
            $selectedCityName  =  $getUser->City['name'];
        }
        else
        {
            $selectedCityId  =  '';
            $selectedCityName  =  '';
        }
        $getCountry     =   Country::where('id','!=',$getUser['country_id'])->get();
        $getState       =   State::where('id','!=',$getUser['state_id'])->get();
        $getCity        =   City::where('id','!=',$getUser['city_id'])->get();
        return view('admin.profiles.edit',compact('getUser','id','selectedCountryId','selectedCountryName','getCountry','selectedStateId','selectedStateName','getState','selectedCityId','selectedCityName','getCity'));

    }
    // end edit view

    // begin update
    public function update(Request $request)
    {
        $getUser    =   User::where('id', Auth::user()->id)->first();
        $updateUser =   User::where('id', Auth::user()->id);
        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/user_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $selectedFilename                 =   $filename;
            }
            else
            {
                $selectedFilename                 =   $getUser['image'];
            }

        }
        else
        {
            $selectedFilename                 =   $getUser['image'];
        }
        // if($request->status ==  0)
        // {
        //     $selectedStatus                =   'Inactive';
        // }
        // else
        // {
        //     $selectedStatus                =   'Active';
        // }
        if(!empty($request->password))
        {
            $selectedPassword   =   Hash::make($request->password);
        }
        else
        {
            $selectedPassword   =   $getUser['password'];
        }
        // if(!empty($request->country_id))
        // {
        //     $country_id   =   $request->country_id;
        // }
        // else
        // {
        //     $country_id   =   0;
        // }

        // if(!empty($request->state_id))
        // {
        //     $state_id   =   $request->state_id;
        // }
        // else
        // {
        //     $state_id   =   0;
        // }

        // if(!empty($request->city_id))
        // {
        //     $city_id   =   $request->city_id;
        // }
        // else
        // {
        //     $city_id   =   0;
        // }
        if($updateUser->update([
            // 'approved' => 1,
            'first_name'            =>   $request->fname,
            'last_name'             =>   $request->lname,
            // 'email'                 =>   $request->email,
            'phone'                 =>   $request->mobile,
            'password'              =>   $selectedPassword,
            // 'status'                =>   $selectedStatus,
            // 'role_id'               =>   $request->role,
            'address'               =>   $request->address,
            // 'country_id'            =>   $country_id,
            // 'state_id'              =>   $state_id,
            // 'city_id'               =>   $city_id,
            // 'zip_code'              =>   $request->zip_code,
            'image'                 =>   $selectedFilename,
            'detail'                =>   $request->detail,
            'facebook_url'          =>   $request->fb_url,
            'youtube_url'           =>   $request->youtube_url,
            'twitter_url'           =>   $request->twitter_url,
            'linkedIn_url'          =>   $request->linkedin_url,
            'dob'                   =>   $request->dob,
            'gender'                =>   $request->gender,
            'allergy'               =>   $request->allergy,
            'diet_requirement'      =>   $request->diet_requirement
            // 'date_of_enrollment'    =>   $request->date_of_enrollment,
            // 'date_of_withdraw'      =>   $request->date_of_withdraw
            ]))
        {
                return redirect()->back()->with('success','Profile updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Profile can not be updated');
        }
    }
    // end update
}
