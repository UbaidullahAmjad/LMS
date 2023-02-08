<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\State;
use App\Country;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CountryController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.countries.listing');
    }
    // end listing view

    // begin add view
    public function add()
    {
        $getCountry     =   Country::get();
        return view('admin.countries.add',compact('getCountry'));
    }
    // end add view

    // begin store
    public function store(Request $request)
    {
        $addCountry                        =   new Country;
        $addCountry->name                  =   $request->country;

        if($addCountry->save())
        {
            return redirect()->back()->with('success','Country Added successfully');
        }
        else
        {
            return redirect()->back()->with('error','Country cannot be added');
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
                            3 =>'created_at',
                        );

        $totalData  =   Country::orderBy('id','desc');
        $totalData  =   $totalData->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = Country::offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
            $posts    =   $posts->get();
        }
        else
        {
            $search = $request->input('search.value');

            $posts =  Country::Where(function($query) use ($search)
                            {
                                $query->where('name','LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
            $posts  =   $posts->get();

            $totalFiltered = Country::Where(function($query) use ($search)
                                    {
                                        $query->where('name','LIKE',"%{$search}%");
                                    });
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $key => $post)
            {

                $edit           =   url('/admin/country/edit/'.base64_encode($post->id));
                // $delete         =   url('/admin/user/delete/'.base64_encode($post->id));
                // $image          =   asset("public/user_images/".    $post->image);

                $srNo           =   $key+1;
                $businessCreatedAt      =   explode(' ',$post->created_at);
                $nestedData['id'] = $srNo;
                $nestedData['name'] = $post->name;

                // $nestedData['status'] = $post->status;
                $nestedData['created'] = $businessCreatedAt[0];
                $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
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
        $id                     =   base64_decode($id);
        $getCountry                =   Country::where('id',$id)->first();
        return view('admin.countries.edit',compact('id','getCountry'));

    }
    // end edit view

    // begin update
    public function update(Request $request)
    {
        $updateCountry = Country::where('id', $request->id);

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

        if($updateCountry->update([
            // 'approved' => 1,
            'name'                  =>   $request->country
            ]))
        {
                return redirect()->back()->with('success','Country updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Country can not be updated');
        }
    }
    // end update

    // begin delete
        // public function delete($id)
        // {
        //     // dd($request->status);
        //     $id     =   base64_decode($id);
        //     if(User::where('id',$id)->delete())
        //     {
        //         return redirect()->back()->with('success','User deleted successfully');
        //     }
        //     else
        //     {
        //         return redirect()->back()->with('error','User cannot be deleted');
        //     }
        // }
    // end delete


}
