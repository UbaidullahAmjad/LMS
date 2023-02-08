<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\State;
use App\Country;
use App\Models\Franchise;
use App\User;
use App\Role;
use App\StudentParent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
// use Image;

class UserController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.users.listing');
    }
    // end listing view

    // begin add view
    public function add()
    {
        $getCity        =   City::get();
        $getState       =   State::get();
        $getCountry     =   Country::get();
        $getParent      =   StudentParent::get();
        $franchises      =   Franchise::get();

        return view('admin.users.add', compact('getCity', 'getState', 'getCountry', 'getParent', 'franchises'));
    }
    // end add view

    // begin store
    public function store(Request $request)
    {
        // dd($request->all());
        
        

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

            $parent_id = 0;
            if (!empty($request->parent_id)) {
                $parent_id   =   $request->parent_id;
            }


            $addUser                        =   new User;
            $addUser->first_name            =   $request->fname;
            $addUser->last_name             =   $request->lname;
            $addUser->email                 =   $request->email;
            $addUser->phone                 =   $request->mobile;
            $addUser->password              =   Hash::make($request->password);
            $addUser->parent_id                 =   $parent_id;

            if ($request->status ==  0) {
                $selectedStatus                =   'Inactive';
            } else {
                $selectedStatus                =   'Active';
            }
            $addUser->status                =   $selectedStatus;
            $addUser->role_id               =   $request->role;
            $addUser->address               =   $request->address;
            $addUser->country_id            =   $country_id;
            $addUser->state_id              =   $state_id;
            $addUser->city_id               =   $city_id;
            $addUser->zip_code              =   $request->zip_code;
            $addUser->franchise_id              =   $request->franchise_id;


            if ($request->hasFile('image')) {
                $image                      =   $request->file('image');

                $path                       =   public_path() . '/user_images/';
                $filename                   =    time().$image->getClientOriginalName();
                
                // $img = Image::make($image->getRealPath());
                // // dd($image->getRealPath());
                // $img->resize(90, 90, function ($constraint) {
                //     $constraint->aspectRatio();
                    
                // })->save($path.$filename);

                // dd($img);
                if ($image->move($path, $filename)) {
                    $addUser->image                 =   $filename;
                } else {
                    $addUser->image                 =   '';
                }
            } else {
                $addUser->image                 =   '';
            }



            $addUser->detail                =   $request->detail;
            $addUser->facebook_url          =   $request->fb_url;
            $addUser->youtube_url           =   $request->youtube_url;
            $addUser->twitter_url           =   $request->twitter_url;
            $addUser->linkedIn_url          =   $request->linkedin_url;
            $addUser->dob                   =   $request->dob;
            $addUser->gender                =   $request->gender;
            $addUser->allergy               =   $request->allergy;
            $addUser->diet_requirement      =   $request->diet_requirement;
            $addUser->date_of_enrollment    =   $request->date_of_enrollment;
            $addUser->date_of_withdraw      =   $request->date_of_withdraw;

            if ($addUser->save()) {
                return redirect()->back()->with('success', 'User Added successfully');
            } else {
                return redirect()->back()->with('error', 'User cannot be added');
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

        $totalData  =   User::where('role_id', 3)->with('Role');
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
            $posts = User::where('role_id', 3)->with('Role')->offset($start)
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

            $posts =  User::where('role_id', 3)->with('Role')->Where(function ($query) use ($search) {
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

            $totalFiltered = User::where('role_id', 3)->with('Role')->Where(function ($query) use ($search) {
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

                $edit           =   url('/admin/user/edit/' . base64_encode($post->id));
                $updateStatus   =   url('/admin/user/updateStatus/' . base64_encode($post->id));
                $delete         =   url('/admin/user/delete/' . base64_encode($post->id));
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
                <a type="button" onclick="return myFunction()" href=' . $delete . ' title="Close" class="btn btn-transparent btn-xs"><i class="fa fa-times fa fa-white"></i></a>
                <a class="btn btn-success btn-sm" data-toggle="modal" href="#add-remarks' . $post->id . '"><i class="fa fa-plus"></i> Remarks</a>
                <div class="modal fade" id="add-remarks' . $post->id . '">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Update Remarks</h4>
                            </div>
                            <form action=' . route("updateRemarksUser") . ' method="POST" role="form" enctype="multipart/form-data">
                                ' . csrf_field() . '
                                <input type="hidden" name="user" value="' . $post->id . '">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <div>
                                            <label for="">Remarks</label>
                                        </div>
                                        <textarea class="form-control" name="remarks" id="remarks" placeholder="Enter remarks" cols="85" rows="5">' . $post->remarks . '</textarea>
                                    </div>
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
        $franchises      =   Franchise::get();

        $id     =   base64_decode($id);
        $getUser    =   User::where('role_id', 3)->where('id', $id)->first();
        $selectedParentId = User::find($id);                  //
        //dd( $selectedParentId);
        $selectedParentName = StudentParent::where('id', $selectedParentId->parent_id)->first();
        // dd(  $selectedParentName);
        if (!empty($getUser->Country['id'])) {
            $selectedCountryId  =  $getUser->Country['id'];
            $selectedCountryName  =  $getUser->Country['name'];
        } else {
            $selectedCountryId  =  '';
            $selectedCountryName  =  '';
        }
        if (!empty($getUser->State['id'])) {
            $selectedStateId  =  $getUser->State['id'];
            $selectedStateName  =  $getUser->State['name'];
        } else {
            $selectedStateId  =  '';
            $selectedStateName  =  '';
        }
        if (!empty($getUser->City['id'])) {
            $selectedCityId  =  $getUser->City['id'];
            $selectedCityName  =  $getUser->City['name'];
        } else {
            $selectedCityId  =  '';
            $selectedCityName  =  '';
        }
        $getCountry     =   Country::where('id', '!=', $getUser['country_id'])->get();
        $getState       =   State::where('id', '!=', $getUser['state_id'])->get();
        $getCity        =   City::where('id', '!=', $getUser['city_id'])->get();
        $getParent      =   StudentParent::where('id', '!=', $selectedParentId)->get();
        //dd( $getParent);
        return view('admin.users.edit', compact(
            'getUser',
            'id',
            'selectedCountryId',
            'selectedCountryName',
            'getCountry',
            'selectedStateId',
            'selectedStateName',
            'getState',
            'selectedCityId',
            'selectedCityName',
            'getCity',
            'selectedParentId',
            'selectedParentName',
            'getParent',
            'franchises'
        ));
    }
    // end edit view

    // begin update
    public function update(Request $request)
    {
        


        $getUser    =   User::where('id', $request->id)->first();
        $updateUser = User::where('id', $request->id);
        if ($request->hasFile('image')) {
            $image                      =   $request->file('image');
            $path                       =   public_path() . '/user_images';
            $filename                   =   $image->getClientOriginalName() . time() . '.' . $image->getClientOriginalExtension();
            if ($image->move($path, $filename)) {
                $selectedFilename                 =   $filename;
            } else {
                $selectedFilename                 =   $getUser['image'];
            }
        } else {
            $selectedFilename                 =   $getUser['image'];
        }
        if ($request->status ==  0) {
            $selectedStatus                =   'Inactive';
        } else {
            $selectedStatus                =   'Active';
        }

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

        $parent_id = 0;
        if (!empty($request->parent_id)) {
            $parent_id   =   $request->parent_id;
        } else {
            $parent_id   =   0;
        }
        if ($updateUser->update([
            // 'approved' => 1,
            'first_name'            =>   $request->fname,
            'last_name'             =>   $request->lname,
            'parent_id'             =>   $parent_id,

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
            'detail'                =>   $request->detail,
            'facebook_url'          =>   $request->fb_url,
            'youtube_url'           =>   $request->youtube_url,
            'twitter_url'           =>   $request->twitter_url,
            'linkedIn_url'          =>   $request->linkedin_url,
            'dob'                   =>   $request->dob,
            'gender'                =>   $request->gender,
            'allergy'               =>   $request->allergy,
            'diet_requirement'      =>   $request->diet_requirement,
            'date_of_enrollment'    =>   $request->date_of_enrollment,
            'date_of_withdraw'      =>   $request->date_of_withdraw,
            'franchise_id'          =>   $request->franchise_id,

        ])) {
            return redirect()->back()->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->with('error', 'User can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        if (User::where('id', $id)->delete()) {
            return redirect()->back()->with('success', 'User deleted successfully');
        } else {
            return redirect()->back()->with('error', 'User cannot be deleted');
        }
    }
    // end delete

    // begin update status
    public function updateStatus($id)
    {
        $id     =   base64_decode($id);
        $checkUserStatus = User::where('id', $id)->first();

        if ($checkUserStatus['status'] ==  'Active') {
            $selectedStatus                =   'Inactive';
        } else {
            $selectedStatus                =   'Active';
        }
        $updateUserStatus = User::where('id', $id);

        if ($updateUserStatus->update([
            'status'                =>   $selectedStatus
        ])) {
            return redirect()->back()->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->with('error', 'User can not be updated');
        }
    }
    // end update status

    // begin update remarks
    public function updateRemarks(Request $request)
    {
        $addRemarksUser = User::where('id', $request->user);
        if ($addRemarksUser->update([
            'remarks'            =>   $request->remarks
        ])) {
            return redirect()->back()->with('success', 'Remarks updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Remarks can not be updated');
        }
    }
    // end update remarks

}
