<?php
namespace App\Http\Controllers;

use App\Course;
use App\Enrollment;
use App\Mail\SendZoomLinkEmail;
use App\Mail\SendZoomLinkUpdatedEmail;
use App\Mail\SendZoomLinkCancelEmail;

use App\Models\ZoomMeeting;
use App\TeacherDocument;
use App\Traits\ZoomMeetingTrait;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MeetingController extends Controller
{
    use ZoomMeetingTrait;

    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;


    public function show()
    {
        // $meeting = $this->get($id);


        return view('meetings.index', compact('meeting'));
    }

    public function create($id)
    {
        $meetings = ZoomMeeting::where('course_id',base64_decode($id))->get();
        return view('zoom.create_meeting',compact('id','meetings'));
    }

    public function store(Request $request,$id)
    {

        $cid = base64_decode($id);
        $enrollment = Enrollment::where('course_id',$cid)->get();


        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([
                'topic'      => $request->topic,
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($request->start_time),
                'duration'   =>  $request->duration,
                // 'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                // 'timezone'     => 'Asia/Kolkata',
                // 'settings'   => [
                //     'host_video'        => ($data['host_video'] == "1") ? true : false,
                //     'participant_video' => ($data['participant_video'] == "1") ? true : false,
                //     'waiting_room'      => true,
                // ],
            ]),
        ];
		
        $response =  $this->client->post($url.$path, $body);
		
        $all_data = json_decode($response->getBody(), true);

        foreach($enrollment as $e){
            $student = User::find($e->user_id);
            if($student->role_id == 3){
                //Mail::to($student->email)->send(new SendZoomLinkEmail($all_data['join_url'],$all_data['topic']));
            }
        }



        $meeting = new ZoomMeeting();
        $meeting->course_id = $cid;
        $meeting->u_id = $all_data['id'];
        $meeting->uuid = $all_data['uuid'];
        $meeting->type = $all_data['type'];
        $meeting->host_id = $all_data['host_id'];
        $meeting->host_email = $all_data['host_email'];
        $meeting->topic = $all_data['topic'];
        $meeting->status = $all_data['status'];
        $meeting->start_time = $all_data['start_time'];
        $meeting->duration = $all_data['duration'];
        $meeting->start_url = $all_data['start_url'];
        $meeting->join_url = $all_data['join_url'];
        //$meeting->password = $all_data['password'];

        $meeting->save();

        return back()->with('success','Meeting Created Successfully');
    }

    public function update(Request $request, $id)
    {
        $cid = $request->cid;
        $enrollment = Enrollment::where('course_id',$cid)->get();


        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([
                'topic'      => $request->topic,
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($request->start_time),
                'duration'   =>  $request->duration,
                // 'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                // 'timezone'     => 'Asia/Kolkata',
                // 'settings'   => [
                //     'host_video'        => ($data['host_video'] == "1") ? true : false,
                //     'participant_video' => ($data['participant_video'] == "1") ? true : false,
                //     'waiting_room'      => true,
                // ],
            ]),
        ];

        $response =  $this->client->post($url.$path, $body);
        $all_data = json_decode($response->getBody(), true);

        foreach($enrollment as $e){
            $student = User::find($e->user_id);
            if($student->role_id == 3){
                Mail::to($student->email)->send(new SendZoomLinkUpdatedEmail($all_data['join_url'],$all_data['topic']));
            }
        }



        $meeting = ZoomMeeting::find($id);
        $meeting->course_id = $cid;
        $meeting->u_id = $all_data['id'];
        $meeting->uuid = $all_data['uuid'];
        $meeting->type = $all_data['type'];
        $meeting->host_id = $all_data['host_id'];
        $meeting->host_email = $all_data['host_email'];
        $meeting->topic = $all_data['topic'];
        $meeting->status = $all_data['status'];
        $meeting->start_time = $all_data['start_time'];
        $meeting->duration = $all_data['duration'];
        $meeting->start_url = $all_data['start_url'];
        $meeting->join_url = $all_data['join_url'];
        //$meeting->password = $all_data['password'];

        $meeting->save();

        return back()->with('success','Meeting Updated Successfully');
    }

    public function deleteMeeting($id,$cid)
    {
        $enrollment = Enrollment::where('course_id',$cid)->get();
        $course = Course::find($cid);
        $meeting = ZoomMeeting::find($id);
        foreach($enrollment as $e){
            $student = User::find($e->user_id);
            if($student->role_id == 3){
                Mail::to($student->email)->send(new SendZoomLinkCancelEmail($meeting->topic,$course));
            }
        }

        $meeting->delete();

        return back()->with('success','Meeting Cancelled');

    }
}
