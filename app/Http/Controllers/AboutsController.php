<?php

namespace App\Http\Controllers;
use App\Models\About;
use Illuminate\Http\Request;

class AboutsController extends Controller
{
    public function edit(){
        $ab = About::all();
        $about = $ab[0];

        return view('admin.about.edit',compact('about'));
    }

    public function update(Request $request,$id){
        $about = About::find($id);

        if(!empty($request->intro)){
            $about->intro = $request->intro;
            $about->save();
        }
        

        return back()->with('success','Data Updated');
    }

}
