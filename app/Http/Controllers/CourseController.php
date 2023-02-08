<?php

namespace App\Http\Controllers;

use App\Assignment;
use Illuminate\Http\Request;
use App\Course;
use App\Category;
use App\User;
use App\CourseAnnouncementType;
use App\CourseAnnouncement;
use App\CourseCurriculumType;
use App\CourseCurriculum;
use App\CourseDescription;
use App\CourseFile;
use App\CourseWorkingHour;
use App\Lesson;
use App\Quiz;
use App\CurriculumAssignment;
use App\CurriculumLesson;
use App\CurriculumQuiz;
use App\Domain;
use App\Models\CoursePercentage;
use App\Prerequisite;
use App\Section;
use App\section_drip;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    // begin listing view
    public function index()
    {
        // dd('df');
        return view('admin.courses.listing');
    }
    // end listing view


    // begin add view
    public function add()
    {
        // dd('coming');
        $getTeachers        =   User::where('role_id', 2)->get();
        $getCategories      =   Category::where('parent_id', 0)->get();
        $getCourses         =   Course::get();

        return view('admin.courses.add', compact('getTeachers', 'getCategories', 'getCourses'));
    }
    // end add view




    // begin store
    public function store(Request $request)
    {

        $addCourse                        =   new Course;

        if ($request->hasFile('image')) {
            $image                      =   $request->file('image');
            $path                       =   public_path() . '/course_images';
            $name                   =   $image->getClientOriginalName();
            $filename = time() . $name;
            if ($image->move($path, $filename)) {
                $addCourse->image                 =   $filename;
                $addCourse->image1                     =   asset("public/course_images/" .    $filename);
            } else {
                $addCourse->image                 =   '';
                $addCourse->image1                     =  '';
            }
        } else {
            $addCourse->image                 =   '';
        }

        $addCourse->name                    =   $request->course;
        $addCourse->category_id             =   $request->category_id;
        $addCourse->sub_category_id         =   $request->sub_category_id;
        $addCourse->user_id                 =   $request->user_id;
        $addCourse->total_duration          =   $request->total_duration;
        $addCourse->total_lectures          =   $request->total_lectures;
        $addCourse->total_videos            =   $request->total_videos;
        $addCourse->level                   =   $request->level;
        $addCourse->fee                     =   $request->fee;
        $addCourse->teacher_id              =   implode(',', $request->teacher_id);
		$addCourse->topnew 					= $request->topnew;





        if ($addCourse->save()) {
            $parent_course_id = Course::latest()->value('id');

            $prerequisite_course_id = $request->course_id;
			if(!empty($prerequisite_course_id )){
				foreach ($prerequisite_course_id as $c) {
					Prerequisite::create(
						[
							'parent_course_id' =>   $parent_course_id,
							'prerequisite_course_id' => $c
						]
					);
				}
			}
            
            return redirect()->back()->with('success', 'Course Added successfully');
        } else {
            return redirect()->back()->with('error', 'Course cannot be added');
        }
    }
    // end store


    // begin listing
    public function listing(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'id',
            2 => 'name',
            3 => 'created_at',
        );

        // dd($columns);

        $totalData  =   Course::orderBy('id', 'desc');
        if (Auth::user()->role_id    ==  2) {
            $totalData  =   $totalData->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%');
        }
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

        if (empty($request->input('search.value'))) {
            $posts = Course::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            // if(!empty($request->parent_id))
            // {
            //     $posts  =   $posts->where('parent_id',$request->parent_id);
            // }
            // else
            // {
            //     $posts  =   $posts->where('parent_id',0);
            // }
            if (Auth::user()->role_id    ==  2) {
                $posts  =   $posts->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%');
            }
            $posts    =   $posts->get();
        } else {
            $search = $request->input('search.value');

            $posts =  Course::Where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            // if(!empty($request->parent_id))
            // {
            //     $posts  =   $posts->where('parent_id',$request->parent_id);
            // }
            // else
            // {
            //     $posts  =   $posts->where('parent_id',0);
            // }
            if (Auth::user()->role_id    ==  2) {
                $posts  =   $posts->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%');
            }
            $posts  =   $posts->get();

            $totalFiltered = Course::Where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            });
            if (!empty($request->parent_id)) {
                $totalFiltered  =   $totalFiltered->where('parent_id', $request->parent_id);
            }
            if (Auth::user()->role_id    ==  2) {
                $totalFiltered  =   $totalFiltered->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%');
            }
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $key => $post) {
                $getSubCategory         =   Category::where('id', $post->sub_category_id)->first();
                if (!empty($getSubCategory)) {
                    $subCategoryName    =   $getSubCategory['name'];
                } else {
                    $subCategoryName    =   '';
                }

                $teachers   =   array();
                $getTeachers         =   User::whereIn('id', explode(',', $post->teacher_id))->get();
                if (!empty($getTeachers)) {
                    foreach ($getTeachers    as  $teacher) {
                        $teachersName    =   $teacher->first_name;
                        array_push($teachers, $teachersName);
                    }
                } else {
                    $teachersName    =   '';
                }

                $teachesNames   =   implode(',', $teachers);

                $totalSubCategories     =   Course::count();
                $edit                   =   url('/admin/course/edit/' . base64_encode($post->id));
                $delete                 =   url('/admin/course/delete/' . base64_encode($post->id));
                $addpercentage                 =   url('/admin/course/percentage/' . base64_encode($post->id));


                $manage_students        =   url('/admin/course/student/view/' . base64_encode($post->id));
                $add_certificate        =   url('/admin/course/student/certificate/' . base64_encode($post->id));

                if (Auth::user()->role_id    ==  2) {
                    $view                   =   url('/teacher/course/view/' . base64_encode($post->id));
                    $edit                   =   url('/admin/course/edit/' . base64_encode($post->id));
                    $zoom_meeting                   =   url('/teacher/meeting/create/' . base64_encode($post->id));
                } else {
                    $view                   =   url('/admin/course/view/' . base64_encode($post->id));
                }
                $image                          =   asset("public/course_images/" .    $post->image);

                $srNo                           =   $key + 1;
                $businessCreatedAt              =   explode(' ', $post->created_at);



                $nestedData['id']               = $srNo;
                $nestedData['image']            = '<img src="' . $image . '" style="height:80px;width:80px;">';
                $nestedData['name']             = $post->name;
                $nestedData['category']         = $post->Category['name'];
                $nestedData['sub_category']     = $subCategoryName;
                $nestedData['user']             = $teachesNames;
                // $nestedData['total_reviews']    = $post->total_reviews;
                // $nestedData['total_ratings']    = $post->total_ratings;
                // $nestedData['total_enrolled']   = $post->total_enrolled;
                // $nestedData['sub_category'] = '<a href="'.$subCategories.'">'.$totalSubCategories.'</a>';

                // $nestedData['status'] = $post->status;
                // $nestedData['created'] = $businessCreatedAt[0];
                if (Auth::user()->role_id    ==  2) {
                    $image = "https://cplusoft.com/projects/LMS/public/images/zoom.png";
                    $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                        <a type="button" title="Edit" class="btn btn-transparent btn-xs" href=' . $view . ' ><i class="fa fa-eye"></i></a>
                        </div>
                        <blink><a title="Create Meeting" class="btn btn-transparent btn-xs" href=' . $zoom_meeting . ' ><img class="zoom-img" src="' . $image . '"></a></blink>
                        </div>';
                } else {
                    $token = csrf_token();
                    $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                        <a type="button" title="Edit" class="btn btn-transparent btn-xs" href=' . $view . ' ><i class="fa fa-eye"></i></a>
                        <a type="button" title="Edit" class="btn btn-transparent btn-xs" href=' . $edit . ' ><i class="fa fa-pencil"></i></a>
                        <a type="button" title="Edit" class="btn btn-transparent btn-xs" href=' . $delete . ' ><i class="fa fa-times fa fa-white"></i></a>
                        <a type="button" title="Manage Student" class="btn btn-transparent btn-xs" href=' . $manage_students . '>Manage Student</a>
                        <button title="Attach Files" class="btn btn-transparent btn-xs" data-toggle="modal" data-target="#exampleModall' . $post->id . '"><i class="fa fa-paperclip"></i></button>
                        <button title="Add Certificate" class="btn btn-transparent btn-xs" data-toggle="modal" data-target="#exampleModal' . $post->id . '">Certificate</button>
                        <button title="Add Percentage" class="btn btn-transparent btn-xs" data-toggle="modal" data-target="#percentage' . $post->id . '">Add %</button>




                        </div>

                        <div class="modal fade" id="percentage' . $post->id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Add Percentages</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="/admins/percentages/' . $post->id . '" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="' . $token . '" />
                                <div class="modal-body">
									<div class="row">
										<center>
										<button class="add_field_button btn btn-primary" title="Add More Percentages" type="button" onclick="addMorePercentages()"> <i class="fa fa-plus-square"></i> Add More</button>
									</center>
									</div>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <lable>File</label>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input_fields_wrap">
                                                <input type="file" name="images[]" class="form-control">
                                                <input type="number" size="2" placeholder="Add Percentage" name="percentage[]" class="form-control"><br>

                                            </div>
                                        </div>
                                        
                                </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                                </form>
                                </div>
                            </div>
                            </div>

                        <div class="modal fade" id="exampleModall' . $post->id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Attach Files</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="/admin/attachfiles/' . $post->id . '" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="' . $token . '" />
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <lable>File</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="input_fields_wrap">
                                                <input type="file" name="files[]" class="form-control"><br>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                        <button class="add_field_button btn btn-primary" title="Add More Files" type="button" onclick="addMoreFiles()"> <i class="fa fa-plus-square"></i></button>
                                    </div>
                                </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                                </form>
                                </div>
                            </div>
                            </div>

                            <div class="modal fade" id="exampleModal' . $post->id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Attach Files</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="/admin/addcertificate/' . $post->id . '" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="' . $token . '" />
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <lable>Add Certificate</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="input_fields_wrap">
                                                <input type="file" name="file" class="form-control"><br>
                                            </div>
                                        </div>

                                </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                                </form>
                                </div>
                            </div>
                            </div>
                            ';
                }





                $data[] = $nestedData;
            }
        } else {
            $data[] = '';
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

    //attach files
    public function attachFiles(Request $request, $id)
    {
        // dd($request->all());

        $files = $request['files'];
        // dd($request->all());
        if (!empty($files)) {

            for ($i = 0; $i < count($files); $i++) {

                $file = $files[$i];
                // dd($file);
                $name = $file->getClientOriginalName();

                $fileName = time() . $name;

                $file->move(storage_path() . '/app/public/', $fileName);

                $nfile = new CourseFile();
                $nfile->course_id = $id;
                $nfile->file = $fileName;
                $nfile->save();
            }
        }

        return back();
    }

    public function percentages(Request $request, $id)
    {
        // dd($request->all());

        $files = $request['images'];
        $percentages = $request['percentage'];


        // dd($request->all());
        if (!empty($files)) {

            for ($i = 0; $i < count($files); $i++) {

                $file = $files[$i];
                // dd($file);
                $name = $file->getClientOriginalName();

                $fileName = time() . $name;

                $file->move(storage_path() . '/app/public/', $fileName);
                $f = '/storage/app/public/' . time() . $name;

                $nfile = new CoursePercentage();
                $nfile->course_id = $id;
                $nfile->file = $f;
                $nfile->percentage = $percentages[$i];
                $nfile->save();
            }
        }

        return back();
    }


    public function addCertificate(Request $request, $id)
    {
        // dd($request->all());

        $file = $request['file'];
        // dd($request->all());
        if (!empty($file)) {


            // dd($file);
            $name = $file->getClientOriginalName();

            $fileName = time() . $name;

            $file->move(storage_path() . '/app/public/', $fileName);

            $course = Course::find($id);
            $course->certificate = $fileName;
            $course->save();
        }

        return back();
    }

    // begin edit view
    public function edit($id)
    {
		
        $id                                 =   base64_decode($id);

        $getCourse                          =   Course::where('id', $id)->first();
        $getSelectedParentCategory          =   Category::where('id', $getCourse['category_id'])->first();
        $getSelectedSubCategory             =   Category::where('id', $getCourse['sub_category_id'])->first();
        $getSelectedPrerequisites           =   Prerequisite::where('parent_course_id', $id)->get();
        $getCourses                         =   Course::all();


        $getSelectedTeachers                =   User::where('role_id', 2)->whereIn('id', explode(',', $getCourse['teacher_id']))->get();
        $getTeachers                        =   User::where('role_id', 2)->whereNotIn('id', explode(',', $getCourse['teacher_id']))->get();
        $getCategories                      =   Category::where('parent_id', 0)->where('id', '!=', $getCourse['category_id'])->get();
        $getSubCategories                   =   Category::where('parent_id', $getCourse['category_id'])->where('id', '!=', $getCourse['sub_category_id'])->get();
        return view('admin.courses.edit', compact('getSelectedPrerequisites', 'getCourses', 'getCourse', 'id', 'getTeachers', 'getSelectedTeachers', 'getCategories', 'getSubCategories', 'getSelectedParentCategory', 'getSelectedSubCategory'));
    }
    // end edit view


    // begin view course
    public function viewCourse($id)
    {
        $id                     =   base64_decode($id);
        $getCourse              =   Course::where('id', $id)->first();
        $getCourseAnnouncementType              =   CourseAnnouncementType::get();
        $getCourseCurriculumType              =   CourseCurriculumType::get();

        $domains = Domain::all();
        // $explodedCreatedAt      =   explode(' ',$getCourse['created_at']);
        // $getCourse['created_at']    =   $explodedCreatedAt[0];
        // $getCourseReview        =   CourseReview::where('course_id',$id)->first();
        $teachers   =   array();
        $getTeachers         =   User::whereIn('id', explode(',', $getCourse['teacher_id']))->get();
        if (!empty($getTeachers)) {
            foreach ($getTeachers    as  $teacher) {
                $teachersName    =   $teacher->first_name;
                array_push($teachers, $teachersName);
            }
        } else {
            $teachersName    =   '';
        }

        $teachesNames   =   implode(',', $teachers);

        $quizes = Quiz::orderby('id','desc')->get();
        $lessons = Lesson::orderby('id','desc')->get();
        $assignments = Assignment::orderby('id','desc')->get();



        $sections = Section::where('course_id', $id)->orderby('index', 'asc')->get();

        $section_drip = section_drip::where(['course_id' => $id])->get();



        return view('admin.courses.view', compact('section_drip', 'sections', 'quizes', 'lessons', 'assignments', 'getCourse', 'teachesNames', 'id', 'getCourseAnnouncementType', 'getCourseCurriculumType', 'domains'));
    }
    // end view course

    // begin update
    public function update(Request $request)
    {
        $getCourse = Course::where('id', $request->id)->first();
        $updateCourse = Course::where('id', $request->id);
        $courseImage = "";
        $courseImage1 = "";
        if ($request->hasFile('image')) {
            $image                      =   $request->file('image');
            $path                       =   public_path() . '/course_images';
            $name                   =   $image->getClientOriginalName();
            $filename = time() . $name;
            if ($image->move($path, $filename)) {
                $getCourse->image                 =   $filename;
                $getCourse->image1                     =   asset("public/course_images/" .    $filename);
				
            } else {
                $getCourse->image                 =   $getCourse['image'];
                $getCourse->image1                     =  $getCourse['image1'];
            }
        }
		
		
        if ($updateCourse->update([
            // 'approved' => 1,
            'name'                    =>   $request->course,
            
            'category_id'             =>   $request->category_id,
            'sub_category_id'         =>   $request->sub_category_id,
            'user_id'                 =>   $request->user_id,
            'total_duration'          =>   $request->total_duration,
            'total_lectures'          =>   $request->total_lectures,
            'total_videos'            =>   $request->total_videos,
            'level'                   =>   $request->level,
			'topnew' 				=> $request->topnew,
            'fee'                     =>   $request->fee,
            'teacher_id'              =>   implode(',', $request->teacher_id)
        ])) {
			$getCourse->save();
            return redirect()->back()->with('success', 'Course updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Course can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        if (Course::where('id', $id)->delete()) {
            return redirect()->back()->with('success', 'User deleted successfully');
        } else {
            return redirect()->back()->with('error', 'User cannot be deleted');
        }
    }
    // end delete

    public function updateIndex(Request $request)
    {

        $section_a = Section::where('course_id', $request->course_id)->where('id', $request->id)->first();
        $section_b = Section::where('course_id', $request->course_id)->where('index', $request->newIndex)->first();

        $section_a->index = $request->newIndex;
        $section_a->save();

        $section_b->index = $request->oldIndex;
        $section_b->save();

        $response = [
            'message' => "updated index"
        ];

        return json_encode($response);
    }


    public function updateInnerIndex(Request $request)
    {

        $sections = Section::where('course_id', $request->course_id)->orderby('index', 'asc')->get();
        foreach ($sections as $section) {
            $curriculums = CourseCurriculum::where('course_id', $request->course_id)
                ->where('section_id', $section->id)->get();
            foreach ($curriculums as $curriculum) {

                $quiz = CurriculumQuiz::where('section_id', $section->id)
                    ->where('quiz_id', $request->id)->first();
                $assing = CurriculumAssignment::where('section_id', $section->id)
                    ->where('assignment_id', $request->id)->first();
                $lesson = CurriculumLesson::where('section_id', $section->id)
                    ->where('lesson_id', $request->id)->first();

                if ($quiz) {
                    $quiz->index = $request->newIndex;
                    $quiz->save();
                    $assing = CurriculumAssignment::where('section_id', $section->id)
                        ->get();
                    $lesson = CurriculumLesson::where('section_id', $section->id)
                        ->get();

                    foreach ($assing as $assign) {
                        if ($assign->index == $request->newIndex) {
                            $assign->index = $request->oldIndex;

                            $assign->save();
                        }
                    }
                    foreach ($lesson as $les) {
                        if ($les->index == $request->newIndex) {
                            $les->index = $request->oldIndex;

                            $les->save();
                        }
                    }
                } elseif ($assing) {

                    $assing->index = $request->newIndex;

                    $assing->save();
                    $quiz = CurriculumQuiz::where('section_id', $section->id)
                        ->get();
                    $lesson = CurriculumLesson::where('section_id', $section->id)
                        ->get();

                    foreach ($quiz as $quizz) {
                        if ($quizz->index == $quizz->newIndex) {
                            $quizz->index = $request->oldIndex;

                            $quizz->save();
                        }
                    }
                    foreach ($lesson as $les) {
                        if ($les->index == $request->newIndex) {
                            $les->index = $request->oldIndex;

                            $les->save();
                        }
                    }
                } elseif ($lesson) {
                    $lesson->index = $request->newIndex;
                    $lesson->save();
                    $quiz = CurriculumQuiz::where('section_id', $section->id)
                        ->get();
                    $assing = CurriculumAssignment::where('section_id', $section->id)
                        ->get();

                    foreach ($quiz as $quizz) {
                        if ($quizz->index == $quizz->newIndex) {
                            $quizz->index = $request->oldIndex;

                            $quizz->save();
                        }
                    }
                    foreach ($assing as $assign) {
                        if ($assign->index == $request->newIndex) {
                            $assign->index = $request->oldIndex;

                            $assign->save();
                        }
                    }
                }
            }
        }


        $response = [
            'message' => "updated index"
        ];

        return json_encode($response);
    }
}
