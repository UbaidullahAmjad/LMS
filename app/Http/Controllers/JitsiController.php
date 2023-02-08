<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\JitsiMeeting;
use Auth;
use App\User;
use App\Course;
use File;

class JitsiController extends Controller
{
    

    public function jitsidashboard()
    {
        $userid = Auth::user()->id;
        $jitsimeeting = JitsiMeeting::where('user_id', $userid)->orderBy('id', 'DESC')->get();
        return view('admin.jitsimeeting.dashboard', compact('jitsimeeting'));
    }

    public function jitsicreate()
    {
        if(Auth::User()->role == "admin"){
            $course = Course::where('status', '1')->get();
          }
          else{
            $course = Course::where('status', '1')->where('user_id', Auth::User()->id)->get();
          }
        return view('admin.jitsimeeting.create', compact('course'));
    }

    public function savejitsimeeting(Request $request){
        $userid = Auth::user()->id;
        $jitsimeeting = new JitsiMeeting();
        $jitsimeeting->meeting_title = $request->topic;
        $jitsimeeting->meeting_id = mt_rand(1000000000, 9999999999);
        $jitsimeeting->start_time = $request->start_time;
        $jitsimeeting->end_time = $request->end_time;
        $jitsimeeting->duration = $request->duration;
        $jitsimeeting->agenda = $request->agenda;
        $jitsimeeting->time_zone = $request->timezone;
        $jitsimeeting->user_id = $userid;
        $jitsimeeting->course_id = $request->course_id;
        $jitsimeeting->link_by = $request->link_by;
        
        if ($request->hasFile('image'))
        {
            $path = 'images/jitsimeet/';

            if(!file_exists(public_path().'/'.$path)) {
                
                $path = 'images/jitsimeet/';
                File::makeDirectory(public_path().'/'.$path,0777,true);
            }

            $image = $request->file('image');
            $name = $image->getClientOriginalName();
            $destinationPath = public_path('images/jitsimeet');
            $image->move($destinationPath, $name);
            $jitsimeeting->image = $name;    
        }
        $jitsimeeting->save();
        return redirect()->back()->with('success','Meeting created successfully');
    }

    public function joinMeetup($meetingid){
        $userid = Auth::user()->id;
        $jitsimeetings = JitsiMeeting::where([
            ['user_id', '=', $userid],
            ['meeting_id', '=', $meetingid]
        ])->get();
        return view('admin.jitsimeeting.jitsimeet', compact('jitsimeetings'));
    }

    public function deletemeeting($meetingid)
    { 
        $userid = Auth::user()->id;
        $jitsimeetings = JitsiMeeting::where([
            ['user_id', '=', $userid],
            ['meeting_id', '=', $meetingid]
        ])->delete();
        return redirect()->back()->with('success','Meeting Deleted successfully !');
    }

    public function jitsidetailpage(Request $request, $id)
    {
       
        $jitsimeet = JitsiMeeting::where('id', $id)->first();
        
        if(!$jitsimeet){
            return redirect('/')->with('delete','Meeting is ended !');
        }
        return view('front.jitsimeet_detail', compact('jitsimeet'));
    }
}
