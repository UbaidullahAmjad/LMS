<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enrollment;
use App\User;

class CourseEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $id= base64_decode($id);
        $enrolled_students= Enrollment::where('course_id','=',$id)->get();
        $students=User::where('role_id','=',3)->get();
        $course_id=$id;
        // dd($id);
        return view('admin.course_enrollment.index' , compact('enrolled_students','students'))->with('users')->with('course_id',$course_id);  //wil display course id and student_id (and student data for now)
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attempt=0;
       try
       {
        $students=$request->student_id ;

        foreach($students as $s)
        {
           $u=Enrollment::where('course_id', $request->course_id)->where('user_id','=', $s)->value('user_id');
           if($u == null)
           {
               ++$attempt;
          Enrollment::create(
                [
       'user_id'=> $s ,
       'course_id'=> $request->course_id

                ]
                );
            }


      }
      if($attempt!=0)
      return back()->with('message','Student enrolled successfully!');
      else
      return back()->with('message','Student cannot be enrolled!');
    }  //try
    catch(Exception $e)
    {
       return back()->with('message','Student cannot be enrolled!');
    }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       // dd($id);

       if(Enrollment::where('id',$id)->delete())
       {
           return redirect()->back()->with('success','Student removed successfully');
       }
       else
       {
           return redirect()->back()->with('error','Student cannot be deleted');
       }
    }
}
