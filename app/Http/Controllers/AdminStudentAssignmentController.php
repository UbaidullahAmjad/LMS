<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use App\Assignment;
use Illuminate\Http\Request;
use App\AssignmentSubmission;
use App\CourseCurriculum;
use App\CurriculumAssignment;
use App\Section;
use App\StudentAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminStudentAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('admin.student_assignments.listing');
    }

    public function viewAssignment($id)
    {
        $id                     =   base64_decode($id);
        // return $id;
        $getStudentAssignment              =   AssignmentSubmission::where('id',$id)->first();
        $getAssignment = Assignment::findorfail($getStudentAssignment->assignment_id);
        $getCourse = Course::findorfail($getStudentAssignment->course_id);
        $getStudent = User::findorfail($getStudentAssignment->student_id);
        // $explodedCreatedAt      =   explode(' ',$getAssignment['created_at']);
        // $getAssignment['created_at']    =   $explodedCreatedAt[0];
        // $getAssignmentReview        =   AssignmentReview::where('Assignment_id',$id)->first();
        // $lessons   =   array();
        // $getLessons         =   Lesson::whereIn('id',explode(',',$getAssignment['lessons_id']))->get();
        // if(!empty($getLessons))
        // {
        //     foreach($getLessons    as  $lesson)
        //     {
        //         $lessonsName    =   $lesson->title;
        //         array_push($lessons,$lessonsName);
        //     }
        // }
        // else
        // {
        //     $lessonsName    =   '';
        // }

        // $lessonsName   =   implode(',',$lessons);
        // $lessonsNames   =   explode(',',$lessonsName);
        //return view('admin.assignments.view',compact('getAssignment','id','lessonsNames'));
        return view('admin.student_assignments.view',compact('getStudentAssignment','getAssignment','getCourse','getStudent','id'));

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

    public function listing(Request $request)
    {
        $columns = array(
                            0 =>'id',
                            1 =>'assignment_id',
                            2 =>'course_id',
                            3 =>'student_id',
                            4 =>'Status',
                            // 4 =>'lesson_id'
                        );

        $totalData  =   AssignmentSubmission::orderBy('id','desc');
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

        // dump($order);

        if(empty($request->input('search.value')))
        {
            $posts = AssignmentSubmission::offset($start)
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
                // $lessons   =   array();
                // $getLessons         =   Lesson::whereIn('id',explode(',',$post->lessons_id))->get();
                // if(!empty($getLessons))
                // {
                //     foreach($getLessons    as  $lesson)
                //     {
                //         $lessonsName    =   $lesson->name;
                //         array_push($lessons,$lessonsName);
                //     }
                // }
                // else
                // {
                //     $lessonsName    =   '';
                // }

                // $lessonsNames   =   implode(',',$lessons);

                $totalSubCategories     =   Assignment::count();
                if(Auth::user()->role_id    ==  2)
                {
                    $edit                   =   url('/admin/student_assignment/edit/'.base64_encode($post->id));
                    $view                   =   url('/admin/student_assignment/view/'.base64_encode($post->id));
                }
                else
                {
                    $edit                   =   url('/admin/student_assignment/edit/'.base64_encode($post->id));
                    $delete                 =   url('/admin/student_assignment/delete/'.base64_encode($post->id));
                    $view                   =   url('/admin/student_assignment/view/'.base64_encode($post->id));

                $image                  =   asset("public/assignment_document/".    $post->file);



                $srNo                           =   $key+1;
                $businessCreatedAt              =   explode(' ',$post->created_at);
                $nestedData['id']               = $srNo;
                // $nestedData['image']            = '<img src="'.$image.'" style="height:80px;width:80px;">';
                $nestedData['assignment Title']            = Assignment::findorfail($post->assignment_id)->title;
                $nestedData['course Name']             = Course::findorfail($post->course_id)->name;
                $nestedData['student Name']         = User::findorfail($post->student_id)->first_name.' '.User::findorfail($post->student_id)->last_name;
                if($post->status == 1)
                {
                    $nestedData['Status']     = "Not checked";
                }
                else if($post->status == 2)
                {
                    $nestedData['Status']     = "Passed";
                }
                else if($post->status == 3)
                {
                    $nestedData['Status']     = "Not Passed";
                }


                $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
                </div>';
                $data[] = $nestedData;

            }
                // for teachers
                $arr = array();
                if(Auth::user()->role_id    ==  2)
                    {
                       $course = Course::find($post->course_id);
                       $section = Section::find($post->section_id);
                       if(!empty($section)){
                        $getAssignments = CurriculumAssignment::where('section_id',$section->id)->get();

                        foreach($getAssignments as $assign){
                             array_push($arr,$assign->assignment_id);
                        }

                        $submit_assignment = Assignment::findorfail($post->assignment_id);
                        if(in_array($submit_assignment->id,$arr)){
                         $srNo                           =   $key+1;
                         $businessCreatedAt              =   explode(' ',$post->created_at);
                         $nestedData['id']               = $srNo;
                         // $nestedData['image']            = '<img src="'.$image.'" style="height:80px;width:80px;">';
                         $nestedData['assignment Title']            = $submit_assignment->title;
                         $nestedData['course Name']             = Course::findorfail($post->course_id)->name;
                         $nestedData['student Name']         = User::findorfail($post->student_id)->first_name.' '.User::findorfail($post->student_id)->last_name;
                         if($post->status == 1)
                         {
                             $nestedData['Status']     = "Not checked";
                         }
                         else if($post->status == 2)
                         {
                             $nestedData['Status']     = "Passed";
                         }
                         else if($post->status == 3)
                         {
                             $nestedData['Status']     = "Not Passed";
                         }


                         $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                         <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                         <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>

                         </div>';
                         $data[] = $nestedData;
                       }

                       }


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
        $id                                 =   base64_decode($id);

        $getStudentAssignment              =   AssignmentSubmission::where('id',$id)->first();
        $getAssignment = Assignment::findorfail($getStudentAssignment->assignment_id);
        $getCourse = Course::findorfail($getStudentAssignment->course_id);
        $getStudent = User::findorfail($getStudentAssignment->student_id);
        // $explodedAssignment   =   explode(',',$getAssignment['lesson_id']);
        // $getSelectedLesson                  =   Lesson::whereIn('id',explode(',',$getAssignment['lessons_id']))->get();
        // // dd($explodedAssignment);
        // $getLessons                         =   Lesson::whereNotIn('id',explode(',',$getAssignment['lessons_id']))->get();

        // $getSelectedParentCategory          =   Category::where('id',$getAssignment['category_id'])->first();
        // $getSelectedSubCategory             =   Category::where('id',$getAssignment['sub_category_id'])->first();

        // $getTeachers                        =   User::where('role_id',2)->where('id','!=',$getAssignment['user_id'])->get();
        // $getCategories                      =   Category::where('parent_id',0)->where('id','!=',$getAssignment['category_id'])->get();
        // $getSubCategories                   =   Category::where('parent_id',$getAssignment['category_id'])->where('id','!=',$getAssignment['sub_category_id'])->get();
        //return view('admin.assignments.edit',compact('id','getAssignment','getSelectedLesson','getLessons'));
        return view('admin.student_assignments.edit',compact('getStudentAssignment','getAssignment','getCourse','getStudent','id'));

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
        // return $request->all();
        // $getAssignment = StudentAssignment::where('id', $request->id)->first();
        $updateAssignment = AssignmentSubmission::findorfail($request->id);
        // return $updateAssignment;

        if($file = $request->file('file'))
        {
            $admin_pic = $file->getClientOriginalName();
            $adm_pic_ext = $file->getClientOriginalExtension();

            // return $admin_pic;

            $adm_pic_hash = Hash::make($admin_pic);

            $adm_pic_hash_replace = str_replace('/','',$adm_pic_hash);

            $adm_pic_full = $adm_pic_hash_replace . ".". $adm_pic_ext;

            // $img->resize(300,300,function($constraint){
            //     $constraint->aspectRatio();
            // })->save('images/'.$adm_pic_full);

            // return $file;

            $file->move(public_path().'/assignment_document/',$adm_pic_full);

            unlink(public_path().'/assignment_document/'.$request->old_file);


            $req['file'] = $adm_pic_full;
        }
        else
        {
            $req['file']                    =   $updateAssignment['file'];
        }

        $req['status'] = $request->assignment_status;
        $req['detail'] = $request->description;

        if($updateAssignment->update($req))
        {
                return redirect()->back()->with('success','Assignment updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Assignment can not be updated');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        if(AssignmentSubmission::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','Assignment deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','Assignment cannot be deleted');
        }
    }
}
