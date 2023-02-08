<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Announcement;
use App\Mail\AnnouncementEmail;
use App\AnnouncementGroup;
use App\AnnouncementHistory;
use App\AnnouncementLog;
use App\User;
use App\Role;
use App\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AnnouncementController extends Controller
{
    //

    public function create()
    {
        return view('admin.announcement.create');
    }

    public function store(Request $request)
    {



   $announcement= new Announcement();
   $announcement->title=  $request->title ;
   $announcement->message=  $request->message  ;
   $announcement->status=  $request->status  ;
   $announcement->expiry_date = $request->expiry_date  ;
   $announcement->announcement_status= '0' ;

if(!empty($request->file))
{
    // $image                      =   $request->file('file');
    // $path                       =   '/storage/app/public/';

    // $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
    // if($image->move($path, $filename))
    // {
    //     $announcement->file                 =   $filename;
    // }
    // else
    // {
    //     $announcement->file                 =   '';
    // }




    $image = $request->file;    // ye line theek ha? yes
        $name = $image->getClientOriginalName();

        $fileName = time() . $name;

        $image->move(storage_path() . '/app/public/', $fileName);
        $announcement->file =   $fileName;
}
else
{
    $announcement->file             =   '';   //ye execute huwi ha shaid aik min
}
if($announcement->save())
return back()->with('success' , 'Announcement created successfully!');
else
return back()->with('fail' , 'Something went wrong!');


    }

// merey pas wesey bhi yaha koi image display nae ho rae , broken show ho rae....ok

public function index()
{

     $announcements=Announcement::get();
     $groups=Group::all();
     return view('admin.announcement.index', compact('announcements','groups'));
}


//tayyba    4/8/21

public function sendAnnouncement(Request $request)
{

  $group = session()->get('groupId');
$data = "";

  if($group != null)
  {

  $id=$request->id;
  $announcement=Announcement::find($id);
  $status=$announcement->status;
  $expiry_date=$announcement->expiry_date;

 if(($status == 1)&&( $expiry_date >= Carbon::now()))
 {

  $msg= $announcement->message;
  try{
Announcement::where('id',$id)->update(
[
    'announcement_status' => 1
]
);
}
catch(Exception $e)
{
return back()->with('fail', 'Something went wrong!');
}

$announcementGroup=AnnouncementGroup::where('group_id', $group[0])->get();


foreach($announcementGroup as $user)
{
$userEmail=User::where('id',$user->user_id)->value('email');


   Mail::to( $userEmail)->send(new AnnouncementEmail($msg));

   $log = AnnouncementLog::where('announcement_id',$announcement->id)
   ->where('sent_to',$user->user_id)
   ->first();

        if(!empty($log)){
            $log->is_read = 0;
            $log->is_received = 0;
            $log->save();
        }else{
            AnnouncementLog::create([
            'announcement_id'=>$announcement->id,
            'sent_from'=> auth()->user()->id ,
            'sent_to'=> $user->user_id ,

            ]);
        }

}

AnnouncementHistory::create([
 'sending_date'=> Carbon::now(),
 'sending_time'=> Carbon::now(),
 'group_id'=>$group[0],
 'announcement_id'=>$id
]);

  
 }
//  else if($expiry_date < Carbon::now())
//  {
//      $data='Sorry, the announcement is expired!';
//  }
	  $data="sent";
session()->put('groupId',[]);
	  return response()->json($data);

}
else
{
$data='Please select announcement group';
	return response()->json($data);
}


}
//tayyba    4/8/21

public function destroy($id)
{
    try
    {
    Announcement::find($id)->delete();

       return back()->with('success','Announcement deleted successfully!');
    }
    catch(Exception $e)
    {
        return back()->with('fail','Something went wrong!');
    }
}






public function edit($id)
{

$announcement=Announcement::find($id);
return view('admin.announcement.edit', compact('announcement'));

}

public function update(Request $request)
{
    $file=$request->file;
    if($file==null)
    {
    try
    {
        Announcement::where('id', $request->id)->update(
            [
'title'   =>     $request->title,
'message' =>     $request->message,
'status' =>      $request->status,
'expiry_date' =>  $request->expiry_date,
'announcement_status' => 0
            ] );

            return back()->with('success','Data updated successfully!');
    }
    catch(Exception $e)
    {
        return back()->with('fail','Something went wrong!');
    }
    }   //  if end

    else
    {
        try
        {
        if(!empty($request->file))
{
        $image = $request->file;    // ye line theek ha? yes
        $name = $image->getClientOriginalName();

        $fileName = time() . $name;

        $image->move(storage_path() . '/app/public/', $fileName);
        $announcement_file =   $fileName;
}

        Announcement::where('id', $request->id)->update(
            [
'title'   =>     $request->title,
'message' =>     $request->message,
'status' =>      $request->status,
'expiry_date' =>  $request->expiry_date,
'file'        =>    $announcement_file ,
'announcement_status' => 0
            ] );

            return back()->with('success','Data updated successfully!');

        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }  //    else end

}

    public function getAnnouncementGroup()
    {
        // $announcementGroup=AnnouncementGroup::pluck('user_id');
        $roles=Role::get();

        $groups=Group::all();
        $groupMembers = [];
        // foreach($announcementGroup as $member)
        // {

        //   $groupMembers[]= User::find($member);
        // }

        return view('admin.announcement.addmembers', compact('roles','groups'));
    }


    public function getUsers(Request $request)   //
    {

   $users= User::where('role_id', $request->role_id)->get();
   return response()->json($users);

    }

    public function getUserId(Request $request)
    {
        session()->push('announcementGroupMembers',$request->id);
    }
    public function getGroupId(Request $request)                //for addmembers view
    {

       session()->push('announcementGroups',$request->id);
    }

//tayyba    4/8/21

    public function groupId(Request $request)                     //for announcement.index
    {
        session()->push('groupId',$request->id);
    }


    public function addToAnnouncementGroup(Request $request)
    {
        $users=$request->username;
        $group_id=$request->group_id;

        foreach($users as $user)
        {
            $member=AnnouncementGroup::where('user_id', $user)->where('group_id',$group_id)->first();

             if($member == null)
             {

    AnnouncementGroup::create(
        [
    'user_id'  =>    $user ,
    'group_id' =>    $group_id
        ]);
             }

             else
             return back()->with('fail','Member already exist in the group!');

        }
        return back()->with('success','Member added successfully!');
    }


}
