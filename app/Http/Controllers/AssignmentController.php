<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lesson;
use App\Assignment;
use App\Course;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AssignmentController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.assignments.listing');
    }
    // end listing view


    // begin add view
    public function add()
    {
        $getLessons         =   Lesson::get();
        return view('admin.assignments.add',compact('getLessons'));
    }
    // end add view




    // begin store
    public function store(Request $request)
    {
        // if(!empty($request->parent))
        // {
        //     $parentId   =   $request->parent;
        // }
        // else
        // {
        //     $parentId   =   0;
        // }
        $addAssignment                        =   new Assignment;

        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/assignment_images';
            $name                   =   $image->getClientOriginalName();
            $filename   = time().$name;
            if($image->move($path, $filename))
            {
                $addAssignment->image               =   $filename;
            }
            else
            {
                $addAssignment->image               =   '';
            }

        }
        else
        {
            $addAssignment->image                   =   '';
        }


        $addAssignment->title                       =   $request->title;
        $addAssignment->description                 =   $request->description;
        $addAssignment->attempt_marks               =   $request->attempt_marks;
        if($addAssignment->save())
        {
            return redirect()->back()->with('success','Assignment Added successfully');
        }
        else
        {
            return redirect()->back()->with('error','Assignment cannot be added');
        }
    }
    // end store


    // begin listing
    public function listing(Request $request)
    {
        $columns = array(
                            0 =>'id',
                            1 =>'id',
                            2 =>'image',
                            3 =>'title',
                            4 =>'lesson_id'
                        );

        $totalData  =   Assignment::orderBy('id','desc');
        // if(!empty($request->parent_id))
        // {
        //     $totalData  =   $totalData->where('parent_id',$request->parent_id);
        // }
        // else
        // {
        //     $totalData  =   $totalData->where('parent_id',0);
        // }
        $totalData  =   $totalData->count();


        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = Assignment::offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                            // if(!empty($request->parent_id))
                            // {
                            //     $posts  =   $posts->where('parent_id',$request->parent_id);
                            // }
                            // else
                            // {
                            //     $posts  =   $posts->where('parent_id',0);
                            // }
            $posts    =   $posts->get();
        }
        else
        {
            $search = $request->input('search.value');

            $posts =  Assignment::Where(function($query) use ($search)
                            {
                                $query->where('title','LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                            // if(!empty($request->parent_id))
                            // {
                            //     $posts  =   $posts->where('parent_id',$request->parent_id);
                            // }
                            // else
                            // {
                            //     $posts  =   $posts->where('parent_id',0);
                            // }
            $posts  =   $posts->get();

            $totalFiltered = Assignment::Where(function($query) use ($search)
                                    {
                                        $query->where('title','LIKE',"%{$search}%");

                                    });
                                    // if(!empty($request->parent_id))
                                    // {
                                    //     $totalFiltered  =   $totalFiltered->where('parent_id',$request->parent_id);
                                    // }
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();


        if(!empty($posts))
        {
            foreach ($posts as $key => $post)
            {
                // $getSubCategory         =   Category::where('id',$post->sub_category_id)->first();
                // if(!empty($getSubCategory))
                // {
                //     $subCategoryName    =   $getSubCategory['name'];
                // }
                // else
                // {
                //     $subCategoryName    =   '';
                // }
                $lessons   =   array();
                $getLessons         =   Lesson::whereIn('id',explode(',',$post->lessons_id))->get();
                if(!empty($getLessons))
                {
                    foreach($getLessons    as  $lesson)
                    {
                        $lessonsName    =   $lesson->name;
                        array_push($lessons,$lessonsName);
                    }
                }
                else
                {
                    $lessonsName    =   '';
                }

                $lessonsNames   =   implode(',',$lessons);

                $totalSubCategories     =   Assignment::count();
                if(Auth::user()->role_id    ==  2)
                {
                    $leson = Lesson::find($post->lesson_id);
                    $course = null;
                    if(!empty($leson)){
                        $course = Course::where('id',$leson->course_id)->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%')->first();
                    }


                    if($course != null){
                        $course = Course::where('id',$leson->course_id)->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%')->first();

                        $edit                   =   url('/teacher/assignment/edit/'.base64_encode($post->id));
                        $view                   =   url('/teacher/assignment/view/'.base64_encode($post->id));

                        $image                  =   asset("public/assignment_images/".    $post->image);

                    $srNo                           =   $key+1;
                    $businessCreatedAt              =   explode(' ',$post->created_at);
                    $nestedData['id']               = $srNo;
                    $nestedData['image']            = '<a href="#">'. $post->image .'</a>';
                    $nestedData['title']            = $post->title;
                    $nestedData['type']             = $post->type;
                    $nestedData['duration']         = $post->duration;
                    $nestedData['available_to']     = $post->available_to;
                    // $nestedData['video_url']        = $post->video_url;
                    $nestedData['allow_comments']   = $post->allow_comments;

                    $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>

                    </div>';
                    $data[] = $nestedData;
                    }

                }
                else
                {
                    $edit                   =   url('/admin/assignment/edit/'.base64_encode($post->id));
                    $delete                 =   url('/admin/assignment/delete/'.base64_encode($post->id));
                    $view                   =   url('/admin/assignment/view/'.base64_encode($post->id));


                    $image                  =   asset("public/assignment_images/".    $post->image);

                $srNo                           =   $key+1;
                $businessCreatedAt              =   explode(' ',$post->created_at);
                $nestedData['id']               = $srNo;
                $nestedData['image']            = '<a href="/admins/file/assignment/download/'.$post->id.'">'. $post->image .'</a>';
                $nestedData['title']            = $post->title;
                $nestedData['type']             = $post->type;
                $nestedData['duration']         = $post->duration;
                $nestedData['available_to']     = $post->available_to;
                // $nestedData['video_url']        = $post->video_url;
                $nestedData['allow_comments']   = $post->allow_comments;

                $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                <a type="button" title="Edit" onclick="return myFunction()" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
                </div>';
                $data[] = $nestedData;
                }

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
        $id                                 =   base64_decode($id);

        $getAssignment                          =   Assignment::where('id',$id)->first();
        // $explodedAssignment   =   explode(',',$getAssignment['lesson_id']);
        $getSelectedLesson                  =   Lesson::whereIn('id',explode(',',$getAssignment['lessons_id']))->get();
        // dd($explodedAssignment);
        $getLessons                         =   Lesson::whereNotIn('id',explode(',',$getAssignment['lessons_id']))->get();

        // $getSelectedParentCategory          =   Category::where('id',$getAssignment['category_id'])->first();
        // $getSelectedSubCategory             =   Category::where('id',$getAssignment['sub_category_id'])->first();

        // $getTeachers                        =   User::where('role_id',2)->where('id','!=',$getAssignment['user_id'])->get();
        // $getCategories                      =   Category::where('parent_id',0)->where('id','!=',$getAssignment['category_id'])->get();
        // $getSubCategories                   =   Category::where('parent_id',$getAssignment['category_id'])->where('id','!=',$getAssignment['sub_category_id'])->get();
        return view('admin.assignments.edit',compact('id','getAssignment','getSelectedLesson','getLessons'));

    }
    // end edit view


    // begin view Assignment
    public function viewAssignment($id)
    {
        $id                     =   base64_decode($id);
        $getAssignment              =   Assignment::where('id',$id)->first();
        // dd($id);
        // $explodedCreatedAt      =   explode(' ',$getAssignment['created_at']);
        // $getAssignment['created_at']    =   $explodedCreatedAt[0];
        // $getAssignmentReview        =   AssignmentReview::where('Assignment_id',$id)->first();


        if(!empty($getAssignment)){
            $lessons   =   array();
            $getLessons         =   Lesson::whereIn('id',explode(',',$getAssignment['lessons_id']))->get();
            if(!empty($getLessons))
            {
                foreach($getLessons    as  $lesson)
                {
                    $lessonsName    =   $lesson->title;
                    array_push($lessons,$lessonsName);
                }
            }
            else
            {
                $lessonsName    =   '';
            }

            $lessonsName   =   implode(',',$lessons);
            $lessonsNames   =   explode(',',$lessonsName);

            return view('admin.assignments.view',compact('getAssignment','id','lessonsNames'));
        }else{
            return back();
        }



    }
    // end view Assignment

    // begin update
    public function update(Request $request)
    {
        $getAssignment = Assignment::where('id', $request->id)->first();
        $updateAssignment = Assignment::where('id', $request->id);

        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/assignment_images';
            $name                   =   $image->getClientOriginalName();
            $filename   = time().$name;
            if($image->move($path, $filename))
            {
                $AssignmentImage                 =   $filename;
            }
            else
            {
                $AssignmentImage                 =   $getAssignment['image'];
            }

        }
        else
        {
            $AssignmentImage                    =   $getAssignment['image'];
        }

        if($updateAssignment->update([
            // 'approved' => 1,
            'title'                     =>   $request->title,
            'image'                     =>   $AssignmentImage,
            'description'               =>   $request->description,
            'attempt_marks'             =>   $request->attempt_marks,


            ]))
        {
                return redirect()->back()->with('success','Assignment updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Assignment can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        if(Assignment::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','Assignment deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','Assignment cannot be deleted');
        }
    }
    // end delete


    public function downloadAssignment($id)
    {
        $file= Assignment::find($id);


        if(!empty($file->image) && file_exists( public_path().'/assignment_images/' . $file->image)){
            $filePath =  public_path('/assignment_images/'.$file->image);

            $fileExt = explode('.',$file->image);

            $fileName = time() . '.' . end($fileExt);
                return response()->download($filePath, $fileName);

        }else{
            return back()->with('error','File does not exist');
        }
    }


}
