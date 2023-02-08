<?php

namespace App\Http\Controllers;

use App\CourseChapter;
use Illuminate\Http\Request;
use DB;
use App\Course;
use Session;
use Image;
use Auth;

class CoursechapterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coursechapter = CourseChapter::all();
        return view('admin.course.coursechapter.index',compact("coursechapter"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $coursechapter = Course::all();
        return view('admin.course.coursechapter.insert',compact('coursechapter'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validate($request,[
            'chapter_name' => 'required',
        ]);

        $input = $request->all();

        if($file = $request->file('file'))
        { 
          $filename = time().$file->getClientOriginalName();
          $file->move('files/material',$filename);
          $input['file'] = $filename;
        }

        $input['position'] = (CourseChapter::count()+1);

        $input['user_id'] = Auth::user()->id;


        $data = CourseChapter::create($input);
        


        $data->save();

        Session::flash('success',trans('flash.AddedSuccessfully'));
        return redirect()->route('course.show',$request->course_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\coursechapter  $coursechapter
     * @return \Illuminate\Http\Response
     */
    public function show( $id)
    {
        $cate = CourseChapter::find($id);
        $courses = Course::all();
        return view('admin.course.coursechapter.edit',compact('cate','courses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\coursechapter  $coursechapter
     * @return \Illuminate\Http\Response
     */
    public function edit(coursechapter $coursechapter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\coursechapter  $coursechapter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $data = $this->validate($request,[
            'chapter_name' => 'required',
        ]);

        $data = CourseChapter::findorfail($id);
        $input = $request->all();

        if(isset($request->status))
        {
            $input['status'] = '1';
        }
        else
        {
            $input['status'] = '0';
        }

        if($file = $request->file('file'))
        {
            if($data->file != "")
            {
                $chapter_file = @file_get_contents(public_path().'/files/material/'.$data->file);

                if($chapter_file)
                {
                    unlink('files/material/'.$data->file);
                }
            }
            $name = time().$file->getClientOriginalName();
            $file->move('files/material', $name);
            $input['file'] = $name;
        }

        $data->update($input);

        Session::flash('success',trans('flash.UpdatedSuccessfully'));
        return redirect()->route('course.show',$request->course_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\coursechapter  $coursechapter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coursechapter = CourseChapter::find($id);

        if ($coursechapter->file != null)
          {
                
            $image_file = @file_get_contents(public_path().'/files/material/'.$coursechapter->file);

            if($image_file)
            {
                unlink(public_path().'/files/material/'.$coursechapter->file);
            }
        }

        DB::table('course_chapters')->where('id',$id)->delete();
        DB::table('course_classes')->where('coursechapter_id',$id)->delete();
        return back(); 
    }

    public function sort(Request $request){
        
        $posts = CourseChapter::all();

        foreach ($posts as $post) {

            foreach ($request->order as $order) {

                if($order['id'] == $post->id) {

                    \DB::table('course_chapters')->where('id',$post->id)->update(['position' => $order['position']]);
                    
                }
            }
        }
        
        return response()->json('Update Successfully.', 200);
    }
}
