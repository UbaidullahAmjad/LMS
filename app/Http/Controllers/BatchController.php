<?php

namespace App\Http\Controllers;

use App\Batch;
use Illuminate\Http\Request;
use App\Course;
use App\User;
Use App\BundleCourse;
Use Auth;
use Redirect;
use DB;
use App\Cart;
use File;
use Image;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $course = Batch::get();
        return view('admin.batch.index', compact('course'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $courses = Course::get();
        $users = User::get();
        $bundles = BundleCourse::get();
        return view('admin.batch.create', compact('courses', 'users', 'bundles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title' => 'required',
            'detail' => 'required',
        ]);

        $input = $request->all();

        $data = Batch::create($input); 

        if(isset($request->type))
        {
          $data->type = "1";
        }
        else
        {
          $data->type = "0";
        }


        if($file = $request->file('preview_image')) 
        {
            $path = 'images/batch/';

            if(!file_exists(public_path().'/'.$path)) {
            
                $path = 'images/batch/';
                File::makeDirectory(public_path().'/'.$path,0777,true);
            }   

            $optimizeImage = Image::make($file);
            $optimizePath = public_path().'/images/batch/';
            $image = time().$file->getClientOriginalName();
            $optimizeImage->save($optimizePath.$image, 72);

            $data->preview_image = $image;
          
        }


        $slug = str_slug($request->title,'-');
        $data->slug = $slug;

        $data->save();

        return redirect('batch');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cor = Batch::find($id);
        $courses = Course::get();
        $users = User::get();
        $bundles = BundleCourse::get();
        return view('admin.batch.edit', compact('cor', 'courses', 'users', 'bundles'));
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
        $request->validate([
          'title' => 'required',
        ]);
          
        $course = Batch::findOrFail($id);
        $input = $request->all();

        if(isset($request->type))
        {
          $input['type'] = "1";
        }
        else
        {
          $input['type'] = "0";
        }

        
        if ($file = $request->file('image')) {

          $path = 'images/batch/';

          if(!file_exists(public_path().'/'.$path)) {
          
              $path = 'images/batch/';
              File::makeDirectory(public_path().'/'.$path,0777,true);
          }  
          
          if($course->preview_image != null) {
            $content = @file_get_contents(public_path().'/images/batch/'.$course->preview_image);
            if ($content) {
              unlink(public_path().'/images/batch/'.$course->preview_image);
            }
          }

          $optimizeImage = Image::make($file);
          $optimizePath = public_path().'/images/batch/';
          $image = time().$file->getClientOriginalName();
          $optimizeImage->save($optimizePath.$image, 72);

          $input['preview_image'] = $image;
          
        }

        $slug = str_slug($input['title'],'-');
        $input['slug'] = $slug;

       

        $course->update($input);

        return redirect('batch');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = Batch::find($id);

        
        if ($course->preview_image != null)
        {
              
            $image_file = @file_get_contents(public_path().'/images/batch/'.$course->preview_image);

            if($image_file)
            {
              unlink(public_path().'/images/batch/'.$course->preview_image);
            }
        } 

        $value = $course->delete();
        

        return redirect('batch');
    }

    public function detailpage(Request $request, $id)
    {
        $batch = Batch::where('id', $id)->first();


        

        $course_id = array();


        array_push($course_id, $batch->allowed_users);

        


        $oo = in_array(Auth::user()->id, $course_id);

        $course_id = array_values(array_filter($course_id));

        $course_id = array_flatten($course_id);


        // foreach($batch->allowed_users as $enrolled)
        // {
            if(Auth::check()){

                if(in_array(Auth::user()->id, $course_id)){

                    return view('front.batch_detail', compact('batch'));
                }
                else{

                    return back()->with('delete', trans('flash.UnauthorizedAction'));

                }
            }
            else{

            return Redirect::route('login')->withInput()->with('delete', trans('flash.PleaseLogin'));

            }

        // }
        
    }

    public function batchcart(Request $request, $id)
    {
        $batchs = Batch::where('id', $id)->first();

        Cart::where('user_id', Auth::User()->id)->delete();


        foreach ($batchs->allowed_courses as $course_id) {

            $course = Course::where('id', $course_id)->first();

            if($course->type == '1')
            {

                DB::table('carts')->insert(
                    array(
                        'user_id' => Auth::User()->id,
                        'course_id' => $course->id,
                        'category_id' => $course->category_id,
                        'price' => $course->price,
                        'offer_price' => $course->discount_price,
                        'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at'  => \Carbon\Carbon::now()->toDateTimeString()

                    )
                );
                
            }
            else{

                DB::table('orders')->insert(
                    array(
                        'user_id' => Auth::User()->id,
                        'instructor_id' => $course->user_id,
                        'course_id' => $course->id,
                        'total_amount' => 'Free',
                        'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
                    )
                );
            }
        }


        return back()->with('success', trans('flash.CartAdded'));
    }
}
