<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use App\Googlemeet;
use App\User;
use Auth;

use App\Course;
use File;

class GoogleMeetController extends Controller
{
    protected $client;

    public function __construct()
    {
        if(file_exists(public_path().'/'.'client_secret.json')) {


            $client = new Google_Client();
            $client->setAuthConfig('client_secret.json');
            $client->addScope(Google_Service_Calendar::CALENDAR);
            $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
            $client->setHttpClient($guzzleClient);
            $this->client = $client;
            
        }

        
    }

    public function dashboard()
    {
        if(file_exists(public_path().'/'.'client_secret.json')) {
            try {
            
                session_start();
                if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
                    $this->client->setAccessToken($_SESSION['access_token']);
                    $service = new Google_Service_Calendar($this->client);
                    $calendarId = 'primary';
                    $results = $service->events->listEvents($calendarId);
                    $allgooglemeet = Googlemeet::where('user_id', '=', Auth::user()->id)->orderBy('id', 'DESC')->get();
                    return view('googlemeet.index',compact('allgooglemeet'));
                   
                } else {
                    return redirect()->route('oauthCallback');
                }
            }catch(\Exception $ex){


                \Session::flash('delete', $ex->getMessage());
                return redirect()->route('googlemeet.setting');

          
            }
        }
        else{

            return redirect()->route('googlemeet.setting')->with('delete','Please update settings !');

        }  
    }

    public function create()
    {
        if(Auth::User()->role == "admin"){
            $course = Course::where('status', '1')->get();
          }
          else{
            $course = Course::where('status', '1')->where('user_id', Auth::User()->id)->get();
          }
        return view('googlemeet.create', compact('course'));
    }

    public function store(Request $request)
    {
        session_start();
        $userid = Auth::user()->id;
        $title = $request->topic;
        $startDateTime = Carbon::parse($request->start_time)->toRfc3339String();
        $endDateTime = Carbon::parse($request->end_time)->toRfc3339String();
        $duration = $request->duration;
        $description = $request->agenda;
        $timezone = $request->timezone;
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
            $service = new Google_Service_Calendar($this->client);

            $calendarId = 'primary';
            $event = new Google_Service_Calendar_Event([
                'summary' => $title,
                'description' => $description,
                'start' => ['dateTime' => $startDateTime],
                'end' => ['dateTime' => $endDateTime],
                'reminders' => ['useDefault' => true],
            ]);
        
            $conference = new \Google_Service_Calendar_ConferenceData();
            $conferenceRequest = new \Google_Service_Calendar_CreateConferenceRequest();
            $conferenceRequest->setRequestId('randomString123');
            $conference->setCreateRequest($conferenceRequest);
            $event->setConferenceData($conference);
            $results = $service->events->insert($calendarId, $event,['conferenceDataVersion' => 1]);
            if(isset($request->link_by))
            {
                $link_by = 'course';
                $course_id = $request['course_id'];
            }
            else
            {
                $link_by = NULL;
                $course_id = NULL;
            }
            $googlemeet = new Googlemeet();
            $googlemeet->meeting_id = $results->id;
            $googlemeet->meeting_title = $results->summary;
            $googlemeet->start_time = $request->start_time;
            $googlemeet->end_time = $request->end_time;
            $googlemeet->meet_url = $results->hangoutLink;
            $googlemeet->agenda = $results->description;
            $googlemeet->duration = $request->duration;
            $googlemeet->timezone = $request->timezone;
            $googlemeet->course_id = $request->course_id;
            $googlemeet->user_id = $userid;
            $googlemeet->link_by = $link_by;
            
            if ($request->hasFile('image'))
            {
                $path = 'images/googlemeet/profile_image/';

                if(!file_exists(public_path().'/'.$path)) {
                    
                    $path = 'images/googlemeet/profile_image/';
                    File::makeDirectory(public_path().'/'.$path,0777,true);
                }

                $image = $request->file('image');
                $name = $image->getClientOriginalName();
                $destinationPath = public_path('images/googlemeet/profile_image');
                $image->move($destinationPath, $name);
                $googlemeet->image = $name;    
            }
            if ($googlemeet->save()) {
                return redirect()->route('googlemeet.index')->with('success','Meeting Schedule successfully !');
            }
            return response()->json(['status' => 'error', 'message' => 'Something went wrong']);
        } else {
            return redirect()->route('oauthCallback');
        }
    }

    public function allgooglemeeting(){
        $allgooglemeet = Googlemeet::where('user_id', '=', Auth::user()->id)->orderBy('id', 'DESC')->get();
        return view('googlemeet.meeting',compact('allgooglemeet'));
    }

    public function oauth()
    {
        if(file_exists(public_path().'/'.'client_secret.json')) {

            session_start();
            $rurl = action('GoogleMeetController@oauth');
            $client = new Google_Client();
            $client->setAuthConfig('client_secret.json');
            $client->setRedirectUri($rurl);
            $client->addScope(Google_Service_Calendar::CALENDAR);
            $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
            $client->setHttpClient($guzzleClient);
           
            if (!isset($_GET['code'])) {
                $auth_url = $client->createAuthUrl();
                $filtered_url = filter_var($auth_url, FILTER_SANITIZE_URL);
                return redirect($filtered_url);
            } else {
                $client->authenticate($_GET['code']);
                $_SESSION['access_token'] = $client->getAccessToken();
                return redirect()->route('googlemeet.index');
            }

        } 
        else{

            return redirect()->route('googlemeet.setting')->with('delete','Please update settings !');

        }  
    }

    public function delete($eventId)
    {
        session_start();
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
            $service = new Google_Service_Calendar($this->client);

            $service->events->delete('primary', $eventId);

            Googlemeet::where('meeting_id',$eventId)->delete();
            return redirect()->route('googlemeet.index')->with('success','Meeting Deleted successfully !');

        } else {
            return redirect()->route('oauthCallback');
        } 
    }

    public function googlemeetdetailpage(Request $request, $id)
    {
        $googlemeet = Googlemeet::where('id', $id)->first();
        if(!$googlemeet){
            return redirect('/')->with('delete','Meeting is ended !');
        }
        return view('front.google_meet_detail', compact('googlemeet'));
    }

    public function edit($id)
    {
        if(Auth::User()->role == "admin"){
            $course = Course::where('status', '1')->get();
          }
          else{
            $course = Course::where('status', '1')->where('user_id', Auth::User()->id)->get();
          }
        $googlemeetedit = Googlemeet::where('meeting_id', $id)->first();
        return view('googlemeet.edit',compact('googlemeetedit', 'course'));
    }

    public function updatemeeting(Request $request, $eventId)
    {
        session_start();
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
            $service = new Google_Service_Calendar($this->client);

            $startDateTime = Carbon::parse($request->start_date)->toRfc3339String();
            $eventDuration = 30; //minutes

            if ($request->has('end_date')) {
                $endDateTime = Carbon::parse($request->end_date)->toRfc3339String();

            } else {
                $endDateTime = Carbon::parse($request->start_date)->addMinutes($eventDuration)->toRfc3339String();
            }

            // retrieve the event from the API.
            $event = $service->events->get('primary', $eventId);
            
            $event->setSummary($request->topic);
            $event->setDescription($request->description);
            //start time
            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($startDateTime);
            $event->setStart($start);

            //end time
            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($endDateTime);
            $event->setEnd($end);

            $updatedEvent = $service->events->update('primary', $event->getId(), $event);
            if (!$updatedEvent) {
                return response()->json(['status' => 'error', 'message' => 'Something went wrong']);
            }
            // === update data into database start
            if(isset($request->link_by))
            {
            $link_by = 'course';
            $course_id = $request['course_id'];
            }
            else
            {
            $link_by = NULL;
            $course_id = NULL;
            }

            Googlemeet::where('meeting_id', $eventId)->update(
                array(
                    'start_time'=> $request->start_date,
                    'end_time'=> $request->end_date,
                    'meeting_title'=> $updatedEvent->description,
                    'agenda' => $updatedEvent->summary,
                    'duration' => $request->duration,
                    'timezone' => $request->timezone,
                    'link_by'=> $link_by,
                    'course_id'=> $course_id,
                    'image' => isset($input['image']),
                    'updated_at'  => \Carbon\Carbon::now()->toDateTimeString(),
                )
              );
            // === update data into database end
            // return response()->json(['status' => 'success', 'data' => $updatedEvent]);
            return redirect()->route('googlemeet.index')->with('success','Meeting Updated successfully !');

        } else {
            return redirect()->route('oauthCallback');
        }
        // =====
    }

    public function googlemeetsetting()
    {
        return view('googlemeet.setting');
    }

    public function googleupdatefile(Request $request){
        $file = $request->file;
        $filext = $file->clientExtension();

        if($request->file != '' && 'json' == $filext){   
        // $file = $request->file;
        $extension =  $file->clientExtension();
        $renamefile = 'client_secret.'.$extension;
        
        }
        // ===
        if($renamefile != ''){        
             $path = public_path()."\\";
            //code for remove old file
                $file_old = $path.$renamefile; 
                if(file_exists($file_old)){
                    unlink($file_old);    
                }
                $query = $file->move($path, $renamefile); 
            if($query){
                return redirect()->route('googlemeet.setting')->with('success','Token details updated successfully !');
            }else{
                return back()->with('delete','Error updating details !');
            }
       }
      }
}
