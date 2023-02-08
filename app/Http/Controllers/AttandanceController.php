<?php

namespace App\Http\Controllers;

use App\Attandance;
use Illuminate\Http\Request;
use Auth;
use App\Course;
use App\Order;
use App\User;

class AttandanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == "admin") 
        {
            $courses = Course::get();
        }
        else{
            $courses = Course::where('user_id', Auth::user()->id)->get();
        }

        $attandance = Attandance::get();
        return view('admin.attandance.index',compact('attandance', 'courses'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Attandance  $attandance
     * @return \Illuminate\Http\Response
     */
    public function show(Attandance $attandance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Attandance  $attandance
     * @return \Illuminate\Http\Response
     */
    public function edit(Attandance $attandance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Attandance  $attandance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attandance $attandance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Attandance  $attandance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attandance $attandance)
    {
        //
    }


    public function enrolled(Request $request, $id)
    {
        $attandance = Attandance::get();
        $enrolled = Order::where('course_id', $id)->get();

        $course = Course::where('id', $id)->first();
        return view('admin.attandance.enrolled',compact('attandance', 'enrolled', 'course'));
    }

    public function attandance(Request $request, $id, $course)
    {
        $user = User::where('id', $id)->first();
        $attandance = Attandance::where('user_id', $id)->where('course_id', $course)->get();

        $enrolled = Order::where('course_id', $course)->where('user_id', $id)->first();
        $course = Course::where('id', $course)->first();
        return view('admin.attandance.attandance',compact('attandance', 'user', 'enrolled', 'course'));
    }
}
