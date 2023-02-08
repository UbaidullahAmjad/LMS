<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AssignmentSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        // return $request->has('file');
        $req=$request->all();
        dd($req);
        // return $request->file('file');
        if($file = $request->file('file'))
        {
            $admin_pic = $file->getClientOriginalName();
            $adm_pic_ext = $file->getClientOriginalExtension();

            // return $admin_pic;

            $adm_pic_hash = Hash::make($admin_pic);

            $adm_pic_hash_replace = str_replace('/','',$adm_pic_hash);

            $adm_pic_full = $adm_pic_hash_replace . ".". $adm_pic_ext;


            $file->move(public_path().'/assignment_document/',$adm_pic_full);

            $req['file'] = $adm_pic_full;
        }
        $req['start_date'] = Carbon::now();

        $req['end_date'] = Carbon::now();

        $req['student_id'] = Auth::user()->id;

        $req['status'] = 1;

        // return $req;
        AssignmentSubmission::create($req);
        return "SUBMITTED";
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
