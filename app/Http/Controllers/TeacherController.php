<?php

namespace App\Http\Controllers;

use App\Announcement;
use App\AnnouncementGroup;
use App\AnnouncementLog;
use Illuminate\Http\Request;
use App\City;
use App\State;
use App\Country;
use App\Models\Franchise;
use App\User;
use App\TeacherDocument;
use Carbon\Carbon;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.teachers.listing');
    }
    // end listing view

    // begin add view
    public function add()
    {
        $getCity        =   City::get();
        $getState       =   State::get();
        $getCountry     =   Country::get();
        $franchises      =   Franchise::get();

        return view('admin.teachers.add', compact('getCity', 'getState', 'getCountry', 'franchises'));
    }
    // end add view

    // begin store
    public function store(Request $request)
    {
        // dd($request->status);
        // $this->validate($request, [
        //     'image' => 'required|dimensions:max_width=90,max_height=90'
        // ]);


        $checkUser  =   User::where('email', $request->email)->first();
        if (!empty($checkUser)) {
            return redirect()->back()->with('error', 'Email already exist in our records');
        } else {
            if (!empty($request->country_id)) {
                $country_id   =   $request->country_id;
            } else {
                $country_id   =   0;
            }

            if (!empty($request->state_id)) {
                $state_id   =   $request->state_id;
            } else {
                $state_id   =   0;
            }

            if (!empty($request->city_id)) {
                $city_id   =   $request->city_id;
            } else {
                $city_id   =   0;
            }

            $addTeacher                        =   new User;
            $addTeacher->first_name            =   $request->fname;
            $addTeacher->last_name             =   $request->lname;
            $addTeacher->email                 =   $request->email;
            $addTeacher->phone                 =   $request->mobile;
            $addTeacher->password              =   Hash::make($request->password);
            if ($request->status ==  0) {
                $selectedStatus                =   'Inactive';
            } else {
                $selectedStatus                =   'Active';
            }
            $addTeacher->status                =   $selectedStatus;
            $addTeacher->role_id               =   $request->role;
            $addTeacher->address               =   $request->address;
            $addTeacher->country_id            =   $country_id;
            $addTeacher->state_id              =   $state_id;
            $addTeacher->city_id               =   $city_id;
            $addTeacher->zip_code              =   $request->zip_code;

            if ($request->hasFile('image')) {
                $image                      =   $request->file('image');
                $path                       =   public_path() . '/user_images';
                $name                   =   $image->getClientOriginalName();
                $filename = time() . $name;
                if ($image->move($path, $filename)) {
                    $addTeacher->image                 =   $filename;
                    $addTeacher->image1                 =   asset("public/user_images/" .    $filename);
                } else {
                    $addTeacher->image                 =   '';
                    $addTeacher->image1                 =  '';
                }
            } else {
                $addTeacher->image                 =   '';
            }



            $addTeacher->detail                =   $request->detail;
            $addTeacher->facebook_url          =   $request->fb_url;
            $addTeacher->youtube_url           =   $request->youtube_url;
            $addTeacher->twitter_url           =   $request->twitter_url;
            $addTeacher->linkedIn_url          =   $request->linkedin_url;
            $addTeacher->franchise_id              =   $request->franchise_id;

            if ($addTeacher->save()) {
                return redirect()->back()->with('success', 'Teacher Added successfully');
            } else {
                return redirect()->back()->with('error', 'Teacher cannot be added');
            }
        }
    }
    // end store


    // begin listing
    public function listing(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'id',
            2 => 'first_name',
            3 => 'last_name',
            4 => 'email',
            5 => 'image',
            6 => 'phone',
            7 => 'role_id',
            8 => 'status',
            9 => 'created_at',
        );

        $totalData  =   User::where('role_id', 2)->with('Role');
        if (!empty($request->status)) {
            $totalData  =   $totalData->where('status', $request->status);
        }
        if (!empty($request->role)) {
            $totalData  =   $totalData->where('role_id', $request->role);
        }
        $totalData  =   $totalData->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = User::where('role_id', 2)->with('Role')->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            if (!empty($request->status)) {
                $posts  =   $posts->where('status', $request->status);
            }
            if (!empty($request->role)) {
                $posts  =   $posts->where('role_id', $request->role);
            }
            $posts    =   $posts->get();
        } else {
            $search = $request->input('search.value');

            $posts =  User::where('role_id', 2)->with('Role')->Where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orwhere('last_name', 'LIKE', "%{$search}%")
                    ->orwhere('email', 'LIKE', "%{$search}%")
                    ->orwhere('phone', 'LIKE', "%{$search}%")
                    ->orwhere('status', 'LIKE', "%{$search}%");
            })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            if (!empty($request->status)) {
                $posts  =   $posts->where('status', $request->status);
            }
            if (!empty($request->role)) {
                $posts  =   $posts->where('role_id', $request->role);
            }
            $posts  =   $posts->get();

            $totalFiltered = User::where('role_id', 2)->with('Role')->Where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orwhere('last_name', 'LIKE', "%{$search}%")
                    ->orwhere('email', 'LIKE', "%{$search}%")
                    ->orwhere('phone', 'LIKE', "%{$search}%")
                    ->orwhere('status', 'LIKE', "%{$search}%");
            });
            if (!empty($request->status)) {
                $totalFiltered  =   $totalFiltered->where('status', $request->status);
            }
            if (!empty($request->role)) {
                $totalFiltered  =   $totalFiltered->where('role_id', $request->role);
            }
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $key => $post) {

                $edit           =   url('/admin/teacher/edit/' . base64_encode($post->id));
                $updateStatus   =   url('/admin/teacher/updateStatus/' . base64_encode($post->id));
                $delete         =   url('/admin/teacher/delete/' . base64_encode($post->id));
                $image          =   asset("public/user_images/" .    $post->image);

                $srNo           =   $key + 1;
                $businessCreatedAt      =   explode(' ', $post->created_at);
                $nestedData['id'] = $srNo;
                $nestedData['image'] = "<img src='" . $image . "' style='width:80px;height:80px;'>";
                $nestedData['name'] = $post->first_name . ' ' . $post->last_name;
                $nestedData['email'] = $post->email;
                // $nestedData['role'] = $post->Role['name'];
                $nestedData['phone'] = $post->phone;
                $nestedData['country'] = $post->Country['name'];
                if ($post->status   ==  'Active') {
                    $nestedData['status'] = '<a type="button" title="Status" class="btn btn-success btn-xs" href=' . $updateStatus . ' >' . $post->status . '</a>';
                } elseif ($post->status   ==  'Inactive') {
                    $nestedData['status'] = '<a type="button" title="Status" class="btn btn-warning btn-xs" href=' . $updateStatus . ' >' . $post->status . '</a>';
                } else {
                    $nestedData['status'] = '<a type="button" title="Status" class="btn btn-danger btn-xs" href="" >' . $post->status . '</a>';
                }
                // $nestedData['status'] = $post->status;
                $nestedData['created'] = $businessCreatedAt[0];
                $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href=' . $edit . ' ><i class="fa fa-pencil"></i></a>
                <a type="button" href=' . $delete . '  onclick="return myFunction()"  title="Close" class="btn btn-transparent btn-xs"><i class="fa fa-times fa fa-white"></i></a>
               
                <a data-toggle="modal" href="#attach-document' . $post->id . '"><i class="fa fa-paperclip"></i></a>
                <div class="modal fade" id="attach-document' . $post->id . '">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Attach Document</h4>
                            </div>
                            <form action=' . route("addTeacherDocument") . ' method="POST" role="form" enctype="multipart/form-data">
                                ' . csrf_field() . '
                                <input type="hidden" name="teacher" value="' . $post->id . '">
                                <div class="modal-body">



                                        <label for="">Choose Document</label>
                                        <input type="file" class="form-control" name="file" id="file" placeholder="Enter label1">

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            'dir' => $dir,
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }
    // end listing
    

   
    // begin edit view
    public function edit($id)
    {
        $id             =   base64_decode($id);
        $getTeacher     =   User::where('id', $id)->first();
        $getCity = "";
        $getCountry = "";
        $getState = "";
        if (!empty($getTeacher['city_id'])) {
            $getCity        =   City::where('id', '!=', $getTeacher['city_id'])->get();
        }
        if (!empty($getTeacher['state_id'])) {
            $getState       =   State::where('id', '!=', $getTeacher['state_id'])->get();
        }
        if (!empty($getTeacher['country_id'])) {
            $getCountry     =   Country::where('id', '!=', $getTeacher['country_id'])->get();
        }
        $franchises      =   Franchise::get();


        if (!empty($getTeacher->City)) {
            $selectedCityId             =   $getTeacher->City['id'];
            $selectedCityName           =   $getTeacher->City['name'];
        } else {
            $selectedCityId             =   '';
            $selectedCityName           =   '';
        }

        if (!empty($getTeacher->State)) {
            $selectedStateId             =   $getTeacher->State['id'];
            $selectedStateName           =   $getTeacher->State['name'];
        } else {
            $selectedStateId             =   '';
            $selectedStateName           =   '';
        }

        if (!empty($getTeacher->Country)) {
            $selectedCountryId             =   $getTeacher->Country['id'];
            $selectedCountryName           =   $getTeacher->Country['name'];
        } else {
            $selectedCountryId             =   '';
            $selectedCountryName           =   '';
        }

        return view('admin.teachers.edit', compact('getTeacher', 'id', 'getCity', 'getState', 'getCountry', 'selectedCityId', 'selectedCityName', 'selectedStateId', 'selectedStateName', 'selectedCountryId', 'selectedCountryName', 'franchises'));
    }
    // end edit view

    // begin update
    public function update(Request $request)
    {
        

        $getTeacher = User::where('id', $request->id)->first();


        $updateTeacher = User::where('id', $request->id);
        if ($request->hasFile('image')) {
            $image                      =   $request->file('image');
            $path                       =   public_path() . '/user_images';
            $name                   =   $image->getClientOriginalName() . time();
            $filename                 = time() . $name;

            $selectedFilename                 =   $filename;
            $selectedFilename1                 =   asset("public/user_images/" .    $filename);
            $image->move($path, $filename);
        } else {

            $selectedFilename                 =   $getTeacher['image'];
            $selectedFilename1                 =   $getTeacher['image1'];
        }
        if ($request->status ==  0) {
            $selectedStatus                =   'Inactive';
        } else {
            $selectedStatus                =   'Active';
        }
        // if(!empty($request->password))
        // {
        //     $selectedPassword   =   Hash::make($request->password);
        // }
        // else
        // {
        //     $selectedPassword   =   '';
        // }
        if (!empty($request->country_id)) {
            $country_id   =   $request->country_id;
        } else {
            $country_id   =   0;
        }

        if (!empty($request->state_id)) {
            $state_id   =   $request->state_id;
        } else {
            $state_id   =   0;
        }

        if (!empty($request->city_id)) {
            $city_id   =   $request->city_id;
        } else {
            $city_id   =   0;
        }
        if ($updateTeacher->update([
            // 'approved' => 1,
            'first_name'            =>   $request->fname,
            'last_name'             =>   $request->lname,
            // 'email'                 =>   $request->email,
            'phone'                 =>   $request->mobile,
            // 'password'              =>   $selectedPassword,
            'status'                =>   $selectedStatus,
            'role_id'               =>   $request->role,
            'address'               =>   $request->address,
            'country_id'            =>   $country_id,
            'state_id'              =>   $state_id,
            'city_id'               =>   $city_id,
            'zip_code'              =>   $request->zip_code,
            'image'                 =>   $selectedFilename,
            'image1'                 =>   $selectedFilename1,

            'detail'                =>   $request->detail,
            'facebook_url'          =>   $request->fb_url,
            'youtube_url'           =>   $request->youtube_url,
            'twitter_url'           =>   $request->twitter_url,
            'linkedIn_url'          =>   $request->linkedin_url,
            'franchise_id'          =>   $request->franchise_id,

        ])) {
            return redirect()->back()->with('success', 'Teacher updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Teacher can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
      
        $id     =   base64_decode($id);
        if (User::where('id', $id)->delete()) {
            return redirect()->back()->with('success', 'Teacher deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Teacher cannot be deleted');
        }
    }
    // end delete

    // begin update status
    public function updateStatus($id)
    {
        $id     =   base64_decode($id);
        $checkTeacherStatus = User::where('id', $id)->first();

        if ($checkTeacherStatus['status'] ==  'Active') {
            $selectedStatus                =   'Inactive';
        } else {
            $selectedStatus                =   'Active';
        }
        $updateTeacherStatus = User::where('id', $id);

        if ($updateTeacherStatus->update([
            'status'                =>   $selectedStatus
        ])) {
            return redirect()->back()->with('success', 'Teacher updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Teacher can not be updated');
        }
    }
    // end update status


    public function attachFiles(Request $request)
    {


        $files = $request['files'];

        if (!empty($files)) {

            for ($i = 0; $i < count($files); $i++) {

                $file = $files[$i];

                $name = $file->getClientOriginalName();

                $fileName = time() . $name;

                $file->move(storage_path() . '/app/public/', $fileName);

                $nfile = new TeacherDocument();
                $nfile->user_id = $request->teacher;
                $nfile->file = $fileName;
                $nfile->save();
            }
        }

        return back();
    }

    public function fileAttachments()
    {
        $id = Auth::user()->id;
        $files = TeacherDocument::where('user_id', $id)->get();
        return view('instructor.attachment.index', compact('files'));
    }

    public function downloadAttachments($id)
    {
        $file = TeacherDocument::find($id);

        if (!empty($file)) {
            if (file_exists(Storage_path('app/public/' . $file->file))) {
                $filePath = Storage_path('app/public/' . $file->file);
                $fileExt = explode('.', $file->file);
                $fileName = time() . '.' . end($fileExt);

                return response()->download($filePath, $fileName);
            } else {
                return back()->with('error', 'File Does not exist on this path');
            }
        } else {
            return back()->with('error', 'File Does not exist');
        }
    }

    public function announcements()
    {

        $new_announcements = AnnouncementLog::where('sent_to', Auth::user()->id)->where('is_received', 0)->get();
        $announcement_array = array();
        foreach ($new_announcements as $new_announcement) {
            array_push($announcement_array, $new_announcement->announcement_id);
        }

        $id = Auth::user()->id;
        $member = AnnouncementGroup::where('user_id', $id)->first();
        $announcement_ids = null;
        if (!empty($member)) {
            AnnouncementLog::where('sent_to', Auth::user()->id)->where('is_received', 0)->update(['is_received' => 1]);
            $announcement_ids = Announcement::join('announcement_logs', 'announcement_logs.announcement_id', 'announcements.id')
                ->where('announcement_logs.sent_to', auth()->user()->id)
                ->get();

            //$announcements= Announcement::where('status','1')->where('expiry_date','>=', Carbon::now())->get();

        } else {
            $announcement = ' ';
        }


        return view('instructor.announcement_v2.announcement', compact('announcement_ids', 'announcement_array'));
    }
    public function viewAnnouncements($id)
    {
        AnnouncementLog::where('announcement_id', $id)->where('sent_to', Auth::user()->id)->update(['is_read' => 1]);
        $announcement = Announcement::where('id', $id)->where('status', '1')->where('expiry_date', '>=', Carbon::now())->first();
        // $announcement= $announcement->where('status','1')->where('expiry_date','>=', Carbon::now())->first();
        return view('instructor.announcement_v2.viewannouncement', compact('announcement'));
    }
    public function downloadAnnouncements($id)
    {
        $file = Announcement::find($id);
        if (!empty($file->file) && file_exists(Storage_path('app/public/' . $file->file))) {
            $filePath = Storage_path('app/public/' . $file->file);

            $fileExt = explode('.', $file->file);

            $fileName = time() . '.' . end($fileExt);
            return response()->download($filePath, $fileName);
        } else {
            return back()->with('error', 'File does not exist');
        }
    }
}
