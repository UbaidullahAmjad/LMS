<?php

namespace App\Http\Controllers;

use App\Announcement;
use App\AnnouncementGroup;
use App\AnnouncementLog;
use App\Course;
use App\Models\Franchise;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\StudentParent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parents = StudentParent::get();

        return view('admin.parent.index', compact('parents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $franchises      =   Franchise::get();

        return view('admin.parent.add', compact('franchises'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //validation
        // dd($request->all());
        try {
            $password = "123456789";
            $user = User::create([
                'name' => $request->father_name,
                'email' => $request->father_email,
                'phone' => $request->father_mobile,
                'address' => $request->address,
                'status' => "active",
                'role_id' => 4,

            ]);
            $user->franchise_id = $request->franchise_id;
            $user->password = Hash::make($password);
            $user->save();
            $parent = StudentParent::create(
                [
                    'user_id' => $user->id,
                    'father_name' => $request->father_name,
                    'mother_name' => $request->mother_name,
                    'father_mobile_number' => $request->father_mobile,
                    'mother_mobile_number' => $request->mother_mobile,
                    'father_email' => $request->father_email,
                    'mother_email' => $request->mother_email,
                    'father_DOB' => $request->father_dob,
                    'mother_DOB' => $request->mother_dob,
                    'address' => $request->address
                ]
            );



            return back()->with('success', 'Data inserted successfully!');
        } catch (Exception $e) {
            return back()->with('fail', 'Something went wrong!');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $parent = StudentParent::find($id);

        $franchises      =   Franchise::get();

        // dd($parent);
        return view('admin.parent.edit', compact('parent', 'franchises'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $parent_id = $request->parent_id;

        StudentParent::where('id', $parent_id)->update(
            [
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'father_mobile_number' => $request->father_mobile,
                'mother_mobile_number' => $request->mother_mobile,
                'father_email' => $request->father_email,
                'mother_email' => $request->mother_email,
                'father_DOB' => $request->father_dob,
                'mother_DOB' => $request->mother_dob,
                'address' => $request->address

            ]
        );
        $student_parent = StudentParent::find($parent_id);
        $parent = User::where('email', $student_parent->father_email)->first();
        $parent->franchise_id = $request->franchise_id;

        if (!empty($request->password) && !empty($parent)) {
            $parent->password = Hash::make($request->password);
            $parent->save();
        }
        $parent->save();

        return back()->with('success', 'Data updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (StudentParent::where('id', $id)->delete()) {
            return back()->with('success', 'Record deleted successfully');
        } else {
            return back()->with('error', 'Record cannot be deleted');
        }
    }


    public function announcements()
    {
        $new_announcements = AnnouncementLog::where('sent_to', Auth::user()->id)->where('is_received', 0)->get();
		$announcement_ids = null;
        $announcement_array = array();
        foreach ($new_announcements as $new_announcement) {
            array_push($announcement_array, $new_announcement->announcement_id);
        }
        $id = Auth::user()->id;
        $member = AnnouncementGroup::where('user_id', $id)->first();
        if (!empty($member)) {
            AnnouncementLog::where('sent_to', Auth::user()->id)->where('is_received', 0)->update(['is_received' => 1]);
            $announcement_ids = Announcement::join('announcement_logs', 'announcement_logs.announcement_id', 'announcements.id')
                ->where('announcement_logs.sent_to', auth()->user()->id)
                ->get();
            // $announcement= Announcement::where('status','1')->where('expiry_date','>=', Carbon::now())->get();
        } else {
            $announcement = ' ';
        }
        return view('parent.announcement', compact('announcement_ids', 'announcement_array'))->with('announcements');
    }

    public function viewAnnouncements($id)
    {

        AnnouncementLog::where('announcement_id', $id)->where('sent_to', Auth::user()->id)->update(['is_read' => 1]);
        $announcement = Announcement::where('id', $id)->where('status', '1')->where('expiry_date', '>=', Carbon::now())->first();
        // $announcement= $announcement->where('status','1')->where('expiry_date','>=', Carbon::now())->first();
        return view('parent.viewannouncement', compact('announcement'));
    }

    public function viewChildren($id)
    {
        $children = User::where('parent_id', $id)->get();
        return view('admin.parent.viewChildren', compact('children'));
    }

    public function viewParentDetail($id)
    {
        $parent = StudentParent::find($id);
        return view('admin.parent.viewParent', compact('parent'));
    }


    // new work





    public function downloadAnnouncements($id)
    {
        $file = Announcement::find($id);
        $filePath = Storage_path('app/public/' . $file->file);
        $fileExt = explode('.', $file->file);
        $fileName = time() . '.' . end($fileExt);
        return response()->download($filePath, $fileName);
    }

    public function viewParentChildren()
    {
        $id = Auth::user()->id;
        $parent = StudentParent::where('user_id', Auth::user()->id)->first();
        if (!empty($parent)) {
            $users = User::where('parent_id', $parent->id)->get();
        } else {
            $users = null;
        }
        //    dd($users);
        return view('parent.viewchildren', compact('users'))->with('Course');
    }


    public function viewReport($u_id, $c_id)
    {

        $user = User::find($u_id);
        $course = Course::find($c_id);
        return view('parent.viewreport', compact('user', 'course'));
    }
}
