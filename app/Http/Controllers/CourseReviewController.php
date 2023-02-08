<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use Auth;

class CourseReviewController extends Controller
{
    public function index()
    {
    	$course = Course::where('user_id', '!=' ,Auth::User()->id)->get();
        return view('admin.course_review.index',compact('course'));
    }

    public function show($id)
    {
    	//return 'yes';
    	$course = Course::where('id' ,$id)->first();
    	return view('admin.course_review.view', compact('course'));

    }

    public function update(Request $request, $id)
    {
        $course = Course::findorfail($id);
        

        if(isset($request->status))
        {

        	Course::where('id', $id)
                    ->update(['reject_txt' => NULL, 'status' => 1]);
            
        }
        else
        { 
        	Course::where('id', $id)
                    ->update(['reject_txt' => $request->reject_txt, 'status' => 0]);
            
        }

        return redirect('coursereview');
    	
    }

    public function rejected()
    {
        //return 'yes';
        $course = Course::where('user_id', Auth::user()->id)->where('reject_txt', '!=', NULL)->get();
        return view('instructor.rejected.index', compact('course'));

    }

    public function rejectedview($id)
    {
        //return 'yes';
        $course = Course::where('id', $id)->first();
        return view('instructor.rejected.view', compact('course'));

    }

}
