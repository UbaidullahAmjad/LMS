<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Lesson;
use App\Category;
use App\LearningGoal;
use App\LessonLearningGoals;
use App\Models\LessonLearningGoal;
use App\Models\QuizLearningGoal;
use App\User;
use Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class LessonController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.lessons.listing');
    }
    // end listing view


    // begin add view
    public function add()
    {
        $getCourses         =   Course::get();
        $lgs = LearningGoal::all();
        return view('admin.lessons.add',compact('getCourses','lgs'));
    }
    // end add view




    // begin store
    public function store(Request $request)
    {
        // dd($request->all());
        // if(!empty($request->parent))
        // {
        //     $parentId   =   $request->parent;
        // }
        // else
        // {
        //     $parentId   =   0;
        // }
        $addLesson                        =   new Lesson;

        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/lesson_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $addLesson->image               =   $filename;
            }
            else
            {
                $addLesson->image               =   '';
            }

        }
        else
        {
            $addLesson->image                   =   '';
        }

        $addLesson->title                       =   $request->title;
        $addLesson->short_description           =   $request->short_description;
        $addLesson->long_description            =   $request->long_description;
        $addLesson->type                        =   $request->type;
        $addLesson->duration                    =   $request->duration;
        $addLesson->available_to                =   $request->available_to;
        $addLesson->video_url                   =   $request->video_url;
        $addLesson->allow_comments              =   $request->allow_comments;
        //$addLesson->course_id                   =   $request->course_id;
        //$addLesson->courses_id                  =   implode(',',$request->courses_id);

        if($addLesson->save())
        {


            return redirect()->back()->with('success','Lesson Added successfully');
        }
        else
        {
            return redirect()->back()->with('error','Lesson cannot be added');
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
                            4 =>'type',
                            5 =>'duration',
                            6 =>'available_to',
                            7 =>'allow_comments',
                            8 =>'course_id'
                        );

        $totalData  =   Lesson::orderBy('id','desc');

        $totalData  =   $totalData->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = Lesson::offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);

            $posts    =   $posts->get();
        }
        else
        {
            $search = $request->input('search.value');

            $posts =  Lesson::Where(function($query) use ($search)
                            {
                                $query->where('title','LIKE',"%{$search}%")
                                ->orwhere('type', 'LIKE',"%{$search}%")
                                ->orwhere('duration', 'LIKE',"%{$search}%")
                                ->orwhere('available_to', 'LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);

            $posts  =   $posts->get();

            $totalFiltered = Lesson::Where(function($query) use ($search)
                                    {
                                        $query->where('title','LIKE',"%{$search}%")
                                        ->orwhere('type', 'LIKE',"%{$search}%")
                                        ->orwhere('duration', 'LIKE',"%{$search}%")
                                        ->orwhere('available_to', 'LIKE',"%{$search}%");

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
                $courses   =   array();

                $getCourses         =   Course::whereIn('id',explode(',',$post->courses_id))->get();
                if(!empty($getCourses))
                {
                    foreach($getCourses    as  $course)
                    {
                        $coursesName    =   $course->name;
                        array_push($courses,$coursesName);
                    }
                }
                else
                {
                    $coursesName    =   '';
                }

                $coursesNames   =   implode(',',$courses);

                $totalSubCategories     =   Lesson::count();

                if(FacadesAuth::user()->role_id    ==  2)
                {
                    $course = Course::where('id',$post->course_id)->where('teacher_id', 'LIKE', '%' . FacadesAuth::user()->id . '%')->first();
                    if(!empty($course)){
                        $edit                   =   url('/teacher/lesson/edit/'.base64_encode($post->id));
                        $view                   =   url('/teacher/lesson/view/'.base64_encode($post->id));


                        $image                  =   asset("public/lesson_images/".    $post->image);



                        $srNo                           =   $key+1;
                        $businessCreatedAt              =   explode(' ',$post->created_at);
                        $nestedData['id']               = $srNo;
                        $nestedData['image']            = '<img src="'.$image.'" style="height:80px;width:80px;">';
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
                    $edit                   =   url('/admin/lesson/edit/'.base64_encode($post->id));
                    $delete                 =   url('/admin/lesson/delete/'.base64_encode($post->id));
                    $view                   =   url('/admin/lesson/view/'.base64_encode($post->id));

                    $image                  =   asset("public/lesson_images/".    $post->image);

                    $srNo                           =   $key+1;
                    $businessCreatedAt              =   explode(' ',$post->created_at);
                    $nestedData['id']               = $srNo;
                    $nestedData['image']            = '<img src="'.$image.'" style="height:80px;width:80px;">';
                    $nestedData['title']            = $post->title;
                    $nestedData['type']             = $post->type;
                    $nestedData['duration']         = $post->duration;
                    $nestedData['available_to']     = $post->available_to;
                    // $nestedData['video_url']        = $post->video_url;
                    $nestedData['allow_comments']   = $post->allow_comments;

                    $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                    <a type="button" title="delete" onclick="return myFunction()" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
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

        $getLesson                          =   Lesson::where('id',$id)->first();
        // $explodedLesson   =   explode(',',$getLesson['courses_id']);
        $getSelectedCourse                  =   Course::whereIn('id',explode(',',$getLesson['courses_id']))->get();
        $lgs                 =   LearningGoal::all();
        $slg = LessonLearningGoals::where('lesson_id',$id)->get();

        // dd($explodedLesson);
        $getCourses                         =   Course::whereNotIn('id',explode(',',$getLesson['courses_id']))->get();


        // $getSelectedParentCategory          =   Category::where('id',$getLesson['category_id'])->first();
        // $getSelectedSubCategory             =   Category::where('id',$getLesson['sub_category_id'])->first();

        // $getTeachers                        =   User::where('role_id',2)->where('id','!=',$getLesson['user_id'])->get();
        // $getCategories                      =   Category::where('parent_id',0)->where('id','!=',$getLesson['category_id'])->get();
        // $getSubCategories                   =   Category::where('parent_id',$getLesson['category_id'])->where('id','!=',$getLesson['sub_category_id'])->get();
        return view('admin.lessons.edit',compact('id','getLesson','getSelectedCourse','getCourses','lgs','slg'));

    }
    // end edit view


    // begin view Lesson
    public function viewLesson($id)
    {
        $id                     =   base64_decode($id);
        $getLesson              =   Lesson::where('id',$id)->first();
        // $explodedCreatedAt      =   explode(' ',$getLesson['created_at']);
        // $getLesson['created_at']    =   $explodedCreatedAt[0];
        // $getLessonReview        =   LessonReview::where('Lesson_id',$id)->first();
        $courses   =   array();
        $getCourses         =   Course::whereIn('id',explode(',',$getLesson['courses_id']))->get();
        if(!empty($getCourses))
        {
            foreach($getCourses    as  $course)
            {
                $coursesName    =   $course->name;
                array_push($courses,$coursesName);
            }
        }
        else
        {
            $coursesName    =   '';
        }

        $coursesNames   =   implode(',',$courses);
        return view('admin.lessons.view',compact('getLesson','id','coursesNames'));

    }
    // end view Lesson

    // begin update
    public function update(Request $request)
    {
        $getLesson = Lesson::where('id', $request->id)->first();
        $updateLesson = Lesson::where('id', $request->id);
        //$lgs = LessonLearningGoals::where('lesson_id',$getLesson->id)->get();

        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/lesson_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $LessonImage                 =   $filename;
            }
            else
            {
                $LessonImage                 =   $getLesson['image'];
            }

        }
        else
        {
            $LessonImage                    =   $getLesson['image'];
        }

        if($updateLesson->update([
            // 'approved' => 1,
            'title'                     =>   $request->title,
            'image'                     =>   $LessonImage,
            'short_description'         =>   $request->short_description,
            'long_description'          =>   $request->long_description,
            'type'                      =>   $request->type,
            'duration'                  =>   $request->duration,
            'available_to'              =>   $request->available_to,
            'video_url'                 =>   $request->video_url,
            'allow_comments'            =>   $request->allow_comments,
           // 'course_id'                 =>   $request->course_id,
           // 'courses_id'                =>   implode(',',$request->courses_id)

            ]))
        {
                return redirect()->back()->with('success','Lesson updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Lesson can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        if(Lesson::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','Lesson deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','Lesson cannot be deleted');
        }
    }
    // end delete


    public function addLGToLesson(Request $request){


        // dd($request->all());
        if(!empty($request->lgs)){
            for($i = 0; $i < count($request->lgs); $i++){
                $lessonlg = new LessonLearningGoal();
                $lessonlg->lesson_id = $request->lesson_id;
                $lessonlg->level_id = $request->level_idd;
                $lessonlg->domain_id = $request->domain_idd;

                $lessonlg->section_id = $request->section_id;
                $lessonlg->lg_id = $request->lgs[$i];

                $lessonlg->save();

            }
        return back()->with('success','Learning Goals Added to Lesson Successfully');

        }

        return back()->with('error','Check atleast one checkbox of learning goals');


    }

    public function removeLessonLgs(Request $request){

        $lesson_goal = LessonLearningGoal::where('lg_id',$request->lg_id)
            ->where('lesson_id',$request->lesson_id)
            ->where('domain_id',$request->domain_id)
            ->where('level_id',$request->level_id)->delete();

            $response = [
                'data' => 'Learning Goal removed from lesson',
            ];

            return json_encode($response);
    }


}
