<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class UserActivityController extends Controller
{
    public function index()
    {
    	$lastActivity = Activity::orderBy('id', 'DESC')->get();

    	return view('admin.user_activity.index', compact('lastActivity'));

    }

    public function delete($id)
    {
    	Activity::where('id',$id)->delete(); 
        return back();
    }
}
