<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = AppSetting::all();

        return view('admin.appsettings.index', compact('settings'));
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
        // dd($request->all());

        $setting = AppSetting::find($id);
        if ($request->hasFile('banner')) {
            $file = $request->banner;
            $name = $file->getClientOriginalName();

            $fileName = time() . $name;

            $file->move(storage_path() . '/app/public/', $fileName);
            $setting->banner =  asset('/storage/app/public/' . $fileName);
        }

        if ($request->hasFile('logo')) {
            $file = $request->logo;
            $name = $file->getClientOriginalName();

            $fileName = time() . $name;

            $file->move(storage_path() . '/app/public/', $fileName);
            $setting->logo = asset('/storage/app/public/' . $fileName);
        }


        $setting->welcome_text = $request->w_text;
        $setting->welcome_heading = $request->w_heading;

        $setting->save();

        return back()->with('success', 'Settings updated');
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
