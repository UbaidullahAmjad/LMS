<?php

namespace App\Http\Controllers;

use App\RefundPolicy;
use Illuminate\Http\Request;
use Session;

class RefundPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $return = RefundPolicy::get();
        return view('admin.return_policy.index',compact('return'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.return_policy.insert');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this -> validate($request,[
            'name'=>'required',
            'days'=>'required',
            'detail'=>'required',
        ]);

        $input = $request->all();
        
        $data = RefundPolicy::create($input);

        Session::flash('success',trans('flash.AddedSuccessfully'));
        return redirect('refundpolicy');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RefundPolicy  $refundPolicy
     * @return \Illuminate\Http\Response
     */
    public function show(RefundPolicy $refundPolicy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RefundPolicy  $refundPolicy
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $return = RefundPolicy::where('id', $id)->first();
        return view('admin.return_policy.edit',compact('return'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RefundPolicy  $refundPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // return $request;

        $data = $this->validate($request,[
            'name' => 'required',
            'detail' => 'required',
            'days' => 'required',
        ]);

        $data = RefundPolicy::findorfail($id);
        $input = $request->all();
        $data->update($input);

       
        Session::flash('success',trans('flash.UpdatedSuccessfully'));
        return redirect('refundpolicy');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RefundPolicy  $refundPolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        RefundPolicy::where('id', $id)->delete();
        return redirect('refundpolicy');
    }
}
