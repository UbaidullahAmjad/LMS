<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class StateController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.states.listing');
    }
    // end listing view

    // begin add view
    public function add()
    {
        return view('admin.states.add');
    }
    // end add view

    // begin store
    public function store(Request $request)
    {
        // dd($request->status);
        if(!empty($request->country_id))
        {
            $country_id   =   $request->country_id;
        }
        else
        {
            $country_id   =   0;
        }

        if(!empty($request->state_id))
        {
            $state_id   =   $request->state_id;
        }
        else
        {
            $state_id   =   0;
        }

        if(!empty($request->city_id))
        {
            $city_id   =   $request->city_id;
        }
        else
        {
            $city_id   =   0;
        }

        $addState                        =   new User;
        $addState->first_name            =   $request->fname;
        $addState->last_name             =   $request->lname;
        $addState->email                 =   $request->email;
        $addState->phone                 =   $request->mobile;
        $addState->password              =   Hash::make($request->password);
        if($request->status ==  0)
        {
            $selectedStatus                =   'Inactive';
        }
        else
        {
            $selectedStatus                =   'Active';
        }
        $addState->status                =   $selectedStatus;
        $addState->role_id               =   $request->role;
        $addState->address               =   $request->address;
        $addState->country_id            =   $country_id;
        $addState->state_id              =   $state_id;
        $addState->city_id               =   $city_id;
        $addState->zip_code              =   $request->zip_code;

        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/user_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $addState->image                 =   $filename;
            }
            else
            {
                $addState->image                 =   '';
            }

        }
        else
        {
            $addState->image                 =   '';
        }



        $addState->detail                =   $request->detail;
        $addState->facebook_url          =   $request->fb_url;
        $addState->youtube_url           =   $request->youtube_url;
        $addState->twitter_url           =   $request->twitter_url;
        $addState->linkedIn_url          =   $request->linkedin_url;
        if($addState->save())
        {
            return redirect()->back()->with('success','User Added successfully');
        }
        else
        {
            return redirect()->back()->with('error','User cannot be added');
        }
    }
    // end store


    // begin listing
    public function listing(Request $request)
    {
        $columns = array(
                            0 =>'id',
                            1 =>'id',
                            2 =>'name',
                            3 =>'email',
                            4 =>'image',
                            5 =>'phone',
                            6 =>'role_id',
                            7 =>'status',
                            8 =>'created_at',
                        );

        $totalData  =   User::with('Role');
        if(!empty($request->status))
        {
            $totalData  =   $totalData->where('status',$request->status);
        }
        if(!empty($request->role))
        {
            $totalData  =   $totalData->where('role_id',$request->role);
        }
        $totalData  =   $totalData->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = User::with('Role')->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                        if(!empty($request->status))
                        {
                            $posts  =   $posts->where('status',$request->status);
                        }
                        if(!empty($request->role))
                        {
                            $posts  =   $posts->where('role_id',$request->role);
                        }
            $posts    =   $posts->get();
        }
        else
        {
            $search = $request->input('search.value');

            $posts =  User::with('Role')->Where(function($query) use ($search)
                            {
                                $query->where('name','LIKE',"%{$search}%")
                                ->orwhere('email', 'LIKE',"%{$search}%")
                                ->orwhere('phone', 'LIKE',"%{$search}%")
                                ->orwhere('status', 'LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                        if(!empty($request->status))
                        {
                            $posts  =   $posts->where('status',$request->status);
                        }
                        if(!empty($request->role))
                        {
                            $posts  =   $posts->where('role_id',$request->role);
                        }
            $posts  =   $posts->get();

            $totalFiltered = User::with('Role')->Where(function($query) use ($search)
                                    {
                                        $query->where('name','LIKE',"%{$search}%")
                                        ->orwhere('email', 'LIKE',"%{$search}%")
                                        ->orwhere('phone', 'LIKE',"%{$search}%")
                                        ->orwhere('status', 'LIKE',"%{$search}%");
                                    });
                                    if(!empty($request->status))
                                    {
                                        $totalFiltered  =   $totalFiltered->where('status',$request->status);
                                    }
                                    if(!empty($request->role))
                                    {
                                        $totalFiltered  =   $totalFiltered->where('role_id',$request->role);
                                    }
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $key => $post)
            {

                $edit           =   url('/admin/user/edit/'.base64_encode($post->id));
                $updateStatus   =   url('/admin/user/updateStatus/'.base64_encode($post->id));
                $delete         =   url('/admin/user/delete/'.base64_encode($post->id));
                $image          =   asset("public/user_images/".    $post->image);

                $srNo           =   $key+1;
                $businessCreatedAt      =   explode(' ',$post->created_at);
                $nestedData['id'] = $srNo;
                $nestedData['image'] = "<img src='".$image."' style='width:80px;height:80px;'>";
                $nestedData['name'] = $post->first_name.' '.$post->last_name;
                $nestedData['email'] = $post->email;
                $nestedData['role'] = $post->Role['name'];
                $nestedData['phone'] = $post->phone;
                $nestedData['country'] = $post->country_id;
                if($post->status   ==  'Active')
                {
                    $nestedData['status']= '<a type="button" title="Status" class="btn btn-success btn-xs" href='.$updateStatus.' >'.$post->status.'</a>';
                }
                elseif($post->status   ==  'Inactive')
                {
                    $nestedData['status']= '<a type="button" title="Status" class="btn btn-warning btn-xs" href='.$updateStatus.' >'.$post->status.'</a>';

                }
                else
                {
                    $nestedData['status']= '<a type="button" title="Status" class="btn btn-danger btn-xs" href="" >'.$post->status.'</a>';
                }
                // $nestedData['status'] = $post->status;
                $nestedData['created'] = $businessCreatedAt[0];
                $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                <a type="button" onclick="return checkDelete()" href='.$delete.' title="Close" class="btn btn-transparent btn-xs"><i class="fa fa-times fa fa-white"></i></a>
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
        $id     =   base64_decode($id);
        $getUser    =   User::where('id',$id)->first();
        return view('admin.states.edit',compact('getUser','id'));

    }
    // end edit view

    // begin update
    public function update(Request $request)
    {
        $updateState = User::where('id', $request->id);
        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/user_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $selectedFilename                 =   $filename;
            }
            else
            {
                $selectedFilename                 =   '';
            }

        }
        else
        {
            $selectedFilename                 =   '';
        }
        if($request->status ==  0)
        {
            $selectedStatus                =   'Inactive';
        }
        else
        {
            $selectedStatus                =   'Active';
        }
        if(!empty($request->password))
        {
            $selectedPassword   =   Hash::make($request->password);
        }
        else
        {
            $selectedPassword   =   '';
        }
        if(!empty($request->country_id))
        {
            $country_id   =   $request->country_id;
        }
        else
        {
            $country_id   =   0;
        }

        if(!empty($request->state_id))
        {
            $state_id   =   $request->state_id;
        }
        else
        {
            $state_id   =   0;
        }

        if(!empty($request->city_id))
        {
            $city_id   =   $request->city_id;
        }
        else
        {
            $city_id   =   0;
        }
        if($updateState->update([
            // 'approved' => 1,
            'first_name'            =>   $request->fname,
            'last_name'             =>   $request->lname,
            'email'                 =>   $request->email,
            'phone'                 =>   $request->mobile,
            'password'              =>   $selectedPassword,
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
            'linkedIn_url'          =>   $request->linkedin_url
            ]))
        {
                return redirect()->back()->with('success','User updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','User can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        if(User::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','User deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','User cannot be deleted');
        }
    }
    // end delete

    // begin update status
    public function updateStatus($id)
    {
        $id     =   base64_decode($id);
        $checkUserStatus = User::where('id', $id)->first();

        if($checkUserStatus['status'] ==  'Active')
        {
            $selectedStatus                =   'Inactive';
        }
        else
        {
            $selectedStatus                =   'Active';
        }
        $updateStateStatus = User::where('id', $id);

        if($updateStateStatus->update([
            'status'                =>   $selectedStatus
            ]))
        {
                return redirect()->back()->with('success','User updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','User can not be updated');
        }
    }
    // end update status

}
