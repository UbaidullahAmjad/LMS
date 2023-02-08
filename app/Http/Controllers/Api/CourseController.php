<?php

namespace App\Http\Controllers\Api;

use App\Announcement;
use App\AnnouncementGroup;
use App\Models\Banner;

use App\AnnouncementLog;
use App\Assignment;
use App\AssignmentSubmission;
use App\Http\Controllers\Controller;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Course;
use App\CourseFaq;
use App\CurriculumAssignment;
use App\CurriculumLesson;
use App\CurriculumQuiz;
use App\Enrollment;
use App\Lesson;
use App\Models\ZoomMeeting;
use App\Quiz;
use App\QuizQuestion;
use App\QuizSubmission;
use App\StudentLesson;

use App\QuizzQuestion;
use App\Section;
use App\StudentParent;
use App\User;
use Auth;
use App\CourseDescription;
use App\Models\AppSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\CourseStart;
use App\Models\CourseRating;
use App\CourseFile;

use Pusher\Pusher;

class CourseController extends Controller
{
    // begin get top courses
    public function topCourses(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $getCourse = Course::orderBy('total_ratings', 'desc')->where('topnew','1')->limit(6)->get();
        if (!empty($getCourse)) {
            // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
            return response()->json([
                'success'               =>  true,
                'data'                  =>  $getCourse
            ], 200);
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Something went wrong'
            ], 200);
        }
        // }
        // else
        // {
        //     return response()->json([
        //         'error'     =>  true,
        //         'message'   => 'You need to login first'
        //     ]);
        // }
    }
    // end get top courses


    // begin get new courses
    public function newCourses(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $getCourse = Course::orderBy('id', 'desc')->limit(6)->get();
        if (!empty($getCourse)) {
            // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
            return response()->json([
                'success'               =>  true,
                'data'                  =>  $getCourse
            ], 200);
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Something went wrong'
            ], 200);
        }
        // }
        // else
        // {
        //     return response()->json([
        //         'error'     =>  true,
        //         'message'   => 'You need to login first'
        //     ]);
        // }
    }
    // end get new courses

    // begin get course details
    public function courseDetails(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $getCourse = Course::with(['CourseDescription', 'CourseCurriculumType', 'CourseFaq', 'CourseReview'])->where('id', $request->id)->first();
        // $getCourse = Course::with(['User','CourseDescription'])->where('id',$request->id)->first();
        if (!empty($getCourse)) {
            if (!empty($getCourse['image'])) {
                $getCourse['image']   =   url('/') . '/public/course_images/' . $getCourse['image'];
            }
            foreach ($getCourse->CourseDescription   as  $courseDescription) {
                if (!empty($courseDescription->image)) {
                    $courseDescription->image   =   url('/') . '/public/course_images/' . $courseDescription->image;
                }
            }

            foreach ($getCourse->CourseReview   as  $courseReview) {
                if (!empty($courseReview->User->image)) {
                    $courseReview->User->image   =   url('/') . '/public/user_images/' . $courseReview->User->image;
                }
            }

            foreach ($getCourse->CourseCurriculumType   as  $courseCurriculumType) {
                // $courseCurriculumType->file    =   11111;

                // if(!empty($courseCurriculumType->CourseCurriculum->file))
                // {
                //     $courseCurriculumType->CourseCurriculum->file   =   url('/').'/public/course_curriculum_type/'.$courseCurriculumType->CourseCurriculum->file;
                // }
            }
            return response()->json([
                'success'               =>  true,
                'data'                  =>  $getCourse
            ], 200);
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Something went wrong'
            ], 200);
        }
        // }
        // else
        // {
        //     return response()->json([
        //         'error'     =>  true,
        //         'message'   => 'You need to login first'
        //     ]);
        // }
    }
    // end get course details

    public function faqs(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $faqs = CourseFaq::orderBy('id', 'desc')->get();
        try {
            if (!empty($faqs)) {
                // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                return response()->json([
                    'success'               =>  true,
                    'data'                  =>  $faqs
                ], 200);
            } else {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something went wrong'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Something went wrong'
            ], 200);
        }

        // }
        // else
        // {
        //     return response()->json([
        //         'error'     =>  true,
        //         'message'   => 'You need to login first'
        //     ]);
        // }
    }


    public function annoucements(Request $request)
    {

        $id = $request->id;
        $user = User::find($id);
        if ($user && $user->role_id == 3) {
            $announcement_ids = "";

            $member = AnnouncementGroup::where('user_id', $id)->first();

            if (!empty($member)) {
                $announcement_ids = Announcement::join('announcement_logs', 'announcement_logs.announcement_id', 'announcements.id')
                    ->where('announcement_logs.sent_to', $id)
                    ->get();
                if (count($announcement_ids) > 0) {
                    return response()->json([
                        'data'             =>  $announcement_ids,
                        'success'           =>  true
                    ], 200);
                } else {
                    return response()->json([
                        'data'             =>  "",
                        'message'           =>  'No Data'
                    ], 200);
                }
            } else {
                return response()->json([
                    'data'             =>  "",
                    'message'           =>  auth()->user()->first_name . " is not a member",
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Login First'
            ], 200);
        }
    }



    public function parents(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {

        $id = $request->id;
        $user = User::find($id);

        if ($user) {
            $parent = StudentParent::find($user->parent_id);

            try {
                if (!empty($parent)) {
                    // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $parent
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'Somethingggg went wrong'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }


    public function myCourses(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);

        if ($user) {
            $enrolls = Enrollment::where('user_id', $id)->get();
            $courses = [];
            $i = 1;
            foreach ($enrolls as $enroll) {
                $course = Course::with(['CourseDescription', 'CourseCurriculumType', 'CourseFaq', 'CourseReview'])->where('id', $enroll->course_id)->first();

                array_push($courses, $course);
            }


            try {
                if (count($courses) > 0) {
                    // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $courses
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Courses Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
	
	    public function myCoursesDesc(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);

        if ($user) {
            $enrolls = Enrollment::where('user_id', $id)->orderBy('created_at','desc')->get();
            $courses = [];
            $i = 1;
            foreach ($enrolls as $enroll) {
                $course = Course::with(['CourseDescription', 'CourseFaq', 'CourseReview'])->where('id', $enroll->course_id)->first();
				

                if(!empty($course)){
					if($course->topnew == 1 || $course->topnew == 2){
						array_push($courses, $course);
					}
					
				}
            }
			
			
			
			


            try {
                if (count($courses) > 0) {
                    // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $courses
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Courses Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
	
	    public function myCoursesDesc5(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);

        if ($user) {
            $enrolls = Enrollment::where('user_id', $id)->orderBy('created_at','desc')->take(5)->get();
            $courses = [];
            $i = 1;
            foreach ($enrolls as $enroll) {
              $course = Course::with(['CourseDescription','CourseCurriculumType','CourseFaq','CourseReview'])->where('id',$enroll->course_id)->first();
				if(!empty($course)){
					if($course->topnew == 0 || $course->topnew == 2){
						array_push($courses, $course);
					}
					
				}
                
            }
			
			


            try {
                if (count($courses) > 0) {
                    // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $courses
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Courses Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }





    public function course_data(Request $request)
    {

        // dump($course_section_id);
        //    dd($course_section_id);




        $course_section = Section::where('course_id', '=', $request->id)->get();
        $course_descrition = CourseDescription::where('course_id', $request->id)->first();
		$test_data = [];
		$check = [];

        $course_curriculum_id = [];
        $course_section_id = [];
        foreach ($course_section as $cs) {
            $course_section_id[] = $cs->id;
        }

        $lesson = [];
        $assignment = [];
        $quiz = [];
		$data = [];
        foreach ($course_section_id as $item_section) {
            $cur_lesson = Section::join('curriculum_lessons','curriculum_lessons.section_id','sections.id')
				->where('sections.id', '=', $item_section)
				->where('sections.course_id',$request->id)->get();
            foreach ($cur_lesson as $item_lesson) {
                if (Lesson::find($item_lesson->lesson_id) != null) {
                    $lesson[] = Lesson::find($item_lesson->lesson_id);
                } else {
                    $lesson = 0;
                }
            }
            $cur_quiz = Section::join('curriculum_quiz','curriculum_quiz.section_id','sections.id')
				->where('sections.id', '=', $item_section)
				->where('sections.course_id',$request->id)->get();
            foreach ($cur_quiz as $item_quiz) {
                if (Quiz::find($item_quiz->quiz_id) != null)
                    $quiz[] = Quiz::find($item_quiz->quiz_id);
            }
            $cur_assignment = CurriculumAssignment::where('section_id', '=', $item_section)->get();
            foreach ($cur_assignment as $item_assignment) {
                if (Assignment::find($item_assignment->assignment_id) !== null)
                    $assignment[] = Assignment::find($item_assignment->assignment_id);
            }
            // dd($lesson);
            //
			
            if (empty($lesson)) {
                $lesson = 0;
            }
            if (empty($quiz)) {
                $quiz = 0;
            }
            if (empty($assignment)) {
                $assignment = 0;
            }
			if(!in_array($item_section,$check)){
				$data[] = array(
					'section' => $item_section, 'lesson' => $lesson, 'assignment' => $assignment, 'quiz' => $quiz,
					'course_section' => $course_section
				);
			
				array_push($test_data,$item_section);
				array_push($check,$data);

			}
			$lesson = [];
        $assignment = [];
        $quiz = [];
			
			
        }

        //if (!empty($lesson) || !empty($quiz) || !empty($assignment)) {
            // dd($lesson);
            // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
            return response()->json([
                'success' =>  true,
                'course_desc' => $course_descrition,
                'data' => $data,
            ], 200);
        
    }
	
	
	
	
	
	public function courseProgress(Request $request)
    {

        // dump($course_section_id);
        // dd($course_section_id);


		$student_id = $request->student_id;
		
		$checkk = CourseStart::where('student_id',$student_id)->where('course_id',$request->id)->first();
		
		
		
		if(!empty($checkk)){
		$sub_total = 0;
		$assign_total = 0;
		$quiz_total = 0;
		$lesson_total = 0;
		
		$l = StudentLesson::where('student_id',$student_id)->where('course_id',$request->id)->get();
		$a = AssignmentSubmission::where('student_id',$student_id)->where('course_id',$request->id)->get();
		$q = QuizSubmission::where('student_id',$student_id)->where('course_id',$request->id)->get();
		if(!empty($l)){
			$lesson_total = count($l);
		}
		if(!empty($a)){
			$assign_total = count($a);
		}
		if(!empty($q)){
			$quiz_total = count($q);
		}
	
		$sub_total = $assign_total + $lesson_total + $quiz_total;
			
		$total = 0;
		

        $course_section = Section::where('course_id', '=', $request->id)->get();
        $course_descrition = CourseDescription::where('course_id', $request->id)->first();
		$test_data = [];
		$check = [];

        $course_curriculum_id = [];
        $course_section_id = [];
        foreach ($course_section as $cs) {
            $course_section_id[] = $cs->id;
        }

        $lesson = [];
        $assignment = [];
        $quiz = [];
		$data = [];
        foreach ($course_section_id as $item_section) {
            $cur_lesson = Section::join('curriculum_lessons','curriculum_lessons.section_id','sections.id')
				->where('sections.id', '=', $item_section)
				->where('sections.course_id',$request->id)->get();
            foreach ($cur_lesson as $item_lesson) {
                if (Lesson::find($item_lesson->lesson_id) != null) {
                    $lesson[] = Lesson::find($item_lesson->lesson_id);
                } else {
                    $lesson = 0;
                }
            }
            $cur_quiz = Section::join('curriculum_quiz','curriculum_quiz.section_id','sections.id')
				->where('sections.id', '=', $item_section)
				->where('sections.course_id',$request->id)->get();
            foreach ($cur_quiz as $item_quiz) {
                if (Quiz::find($item_quiz->quiz_id) != null)
                    $quiz[] = Quiz::find($item_quiz->quiz_id);
            }
            $cur_assignment = CurriculumAssignment::where('section_id', '=', $item_section)->get();
            foreach ($cur_assignment as $item_assignment) {
                if (Assignment::find($item_assignment->assignment_id) !== null)
                    $assignment[] = Assignment::find($item_assignment->assignment_id);
            }
            // dump("LESSS",count($lesson));
            //
			
            
			
			$total = $total + count($assignment) + count($lesson) + count($quiz);
			//return response()->json(count($assignment));
			//dump("ASSIGN",count($lesson));
			
        }
			//return response()->json($total);
			//dump("SUBBBB",$sub_total);
			//dd("END", ($total));
        	$percentage = 0;
			
			if(!empty($total)){
				$percentage = ($sub_total/$total) * 100;
			}
            return response()->json([
                'success' =>  true,
                
                'data' => $percentage,
            ], 200);
		}else{
			return response()->json([
                'error' =>  true,
                
                'message' => "Course not started yet",
            ], 200);
		}
    }

    // new Apis

    public function courses(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $courses = Course::with(['CourseDescription', 'CourseReview'])->get();

        foreach ($courses as $c) {
            $c->image1 = asset("public/course_images/" .    $c->image);
            $c->save();
        }
        try {
            if (count($courses) > 0) {
                // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                return response()->json([
                    'success'               =>  true,
                    'data'                  =>  $courses
                ], 200);
            } else {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'No Courses Available'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'Something444 went wrong'
            ], 200);
        }
    }


    public function searchCourse(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);
        if ($user) {
            $courses = Course::with(['CourseDescription', 'CourseReview'])->where('name', 'like', '%' . $request->search . '%')->get();



            try {
                if (count($courses) > 0) {
                    // $getCourse['image']   =   url('/').'/public/user_images/'.$getCourse['image'];
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $courses
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Courses Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }


    public function myQuizes(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);

        $quizes = [];
        if ($user && $user->role_id == 3) {
         $myquizes = Quiz::join('quiz_submissions', 'quiz_submissions.quiz_id', 'quizzes.id')->join('courses','courses.id','quiz_submissions.course_id')
                ->where('quiz_submissions.student_id', $id)->get();


            try {
                if (count($myquizes) > 0) {

                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $myquizes
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Quiz Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }



    // abhi daali hain

    public function editParentProfile(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);



        if ($user && $user->role_id == 3) {
            $parent = StudentParent::find($user->parent_id);


            try {
                if (!empty($parent)) {
                    $parent_user = User::where('email', $parent->father_email)->first();
                    $parent->update(
                        [
                            'father_name' => $request->father_name,
                            'mother_name' => $request->mother_name,
                            'father_mobile_number' => $request->father_mobile,
                            'mother_mobile_number' => $request->mother_mobile,
                            //'father_email' => $request->father_email,
                            //'mother_email' => $request->mother_email,
                            //'father_DOB' => $request->father_dob,
                            //'mother_DOB' => $request->mother_dob,
                            'address' => $request->address

                        ]
                    );

                    $parent_user->email = $request->father_email;
                    $parent_user->save();
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  "Parent Profile Updated"
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'Not Updated'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }



    public function assignmentSubmission(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $req = $request->all();
        $id = json_decode($request->id);
        $user = User::find($id);



        if ($user && $user->role_id == 3) {


            try {
                if (!empty($request->file('file'))) {
                    $file = $request->file('file');
                    $admin_pic = $file->getClientOriginalName();
                    $adm_pic_ext = $file->getClientOriginalExtension();

                    // return $admin_pic;

                    $adm_pic_hash = Hash::make($admin_pic);

                    $adm_pic_hash_replace = str_replace('/', '', $adm_pic_hash);

                    $adm_pic_full = $adm_pic_hash_replace . "." . $adm_pic_ext;


                    $file->move(public_path() . '/assignment_document/', $adm_pic_full);

                    $req['file'] = $adm_pic_full;
                }
                $req['start_date'] = Carbon::now();

                $req['end_date'] = Carbon::now();

                $req['student_id'] = json_decode($id);

                $req['status'] = 1;



                AssignmentSubmission::updateOrCreate(['student_id'=>$id,'course_id'=>json_decode($request->course_id),'section_id'=>json_decode($request->section_id)],($req));

                return response()->json([
                    'success'               =>  true,
                    'data'                  =>  "Assignment Submitted Successfully"
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }



    public function quizSubmission(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $req = $request->all();
        $id = $request->id;
        $user = User::find($id);

        $answers = $request->answer;
        $questions = $request->questions;






        if ($user && $user->role_id == 3) {


            try {

                $array_questions_id = [];

                if (!empty($request->quiz_id)) {
                    $quiz_question = QuizzQuestion::where('quiz_id', $request->quiz_id)->get();


                    if (count($quiz_question) > 0) {
                        foreach ($questions as $q) {
                            $ques_id = explode("r", $q);

                            $quess_id = $ques_id[1];


                            array_push($array_questions_id, $quess_id);
                        }




                        $total_correct_answer = 0;
                        $i = 0;
                        foreach ($array_questions_id as $question_id) {
                            $question_correct_answer = QuizQuestion::find($question_id);


                            if ($answers[$i] == $question_correct_answer->answer) {

                                $total_correct_answer =  $total_correct_answer + 1;
                            }

                            $i++;
                        }

                        $calculate_percentage = ($total_correct_answer / count($quiz_question)) * 100;

                        $percentage = round($calculate_percentage, 2);



                        $req['marks'] = $percentage;


                        $data = QuizSubmission::updateOrcreate([
                            'quiz_id' => $request->quiz_id, 'course_id' => $request->course_id,
                            'section_id' => $request->section_id, 'student_id' => $id
                        ], $req);
                        $data->marks = $percentage;
						$data->created_at = \Carbon::now();
                        $data->save();
                        return response()->json([
                            'success'               =>  true,
                            'data'                  =>  "Quiz Submitted Successfully"
                        ], 200);
                    } else {
                        return response()->json([
                            'success'               =>  true,
                            'data'                  =>  "No Questions in this quiz"
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  "No quiz"
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }



    public function editStudentProfile(Request $request)
    {
        $req = $request->all();
		//return response()->json($req);
        //$json_Addres=json_decode($request->address);
        //return $json_Addres;
        //	return response()->json(['data'=>$request->id]);
        //if($request->file('image')){
        //$image1=$request->file('image');
        //return response()->json($image1->getClientOriginalName());
        //}
        //else{
        //return response()->json(false);	
        //}
        //return response()->json("END");
        // if (Auth::guard('api')->check())
        // {
        $id = json_decode($request->id);
        $user = User::find($id);



        if ($user && $user->role_id == 3) {


            try {

                if (!empty($request->image)) {
                    $image                      =   $request->file('image');
                    //$image1                      =   $request->image['name'];

                    $path                       =   public_path() . '/user_images';

                    $name                   =   $image->getClientOriginalName();

                    $filename               = time() . $user->id . rand(1, 99999) . '.' . $image->getClientOriginalExtension();



                    if ($image->move($path, $filename)) {
                        $user->image                 =   $filename;
                        $user->image1                  = asset('/public/user_images/' . $filename);
                        $user->save();
                    }
                }
                //return response()->json(true);
				$password = json_decode($request->password);
                if (!empty($password)) {
                    $user->password = Hash::make($password);
                    $user->save();
                }

                // 'approved' => 1,
                //$user->first_name          =   $request->fname;
                //$user->last_name            =   $request->lname;
                //$user->email                =   $request->email;
                // 'password'              =>   $selectedPassword,
                $user->address              =   json_decode($request->address);
                // 'image'                 =>   $selectedFilename,
                //$user->detail               =   json_decode($request->detail);
                //$user->dob                   =  json_decode($request->dob);
                $user->gender              =   json_decode($request->gender);
                $user->allergy              =   json_decode($request->allergy);
                $user->diet_requirement      =  json_decode($request->diet_requirement);
                //$user->date_of_enrollment    =   json_decode($request->date_of_enrollment);
                //$user->date_of_withdraw    =  json_decode($request->date_of_withdraw);
                $user->save();

                return response()->json([
                    'success'               =>  true,
                    'data'                  =>  "Student Profile Updated"
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
    public function quizQuestions(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $req = $request->all();
        $id = $request->id;
        $user = User::find($id);





        if ($user && $user->role_id == 3) {


            try {

                $array_questions_id = [];

                if (!empty($request->quiz_id)) {
                    $quiz_question = QuizzQuestion::where('quiz_id', $request->quiz_id)->get();


                    if (count($quiz_question) > 0) {
                        foreach ($quiz_question as $q) {
                            array_push($array_questions_id, $q->question_id);
                        }




                        $quiz_questions = [];
                        foreach ($array_questions_id as $question_id) {
                            $question = QuizQuestion::find($question_id);
                            if ($question != null) {
                                array_push($quiz_questions, $question);
                            }
                        }


                        return response()->json([
                            'success'               =>  true,
                            'data'                  =>  $quiz_questions
                        ], 200);
                    } else {
                        return response()->json([
                            'success'               =>  true,
                            'message'                  =>  "No Questions in this quiz"
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'success'               =>  true,
                        'message'                  =>  "No quiz"
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
    public function zoomMeetingNotification(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);



        if ($user && $user->role_id == 3) {

            $enrollment = Enrollment::where('course_id', $request->course_id)
                ->where('user_id', $id)->first();


            try {
                if (!empty($enrollment)) {
                    $zoom = ZoomMeeting::where('course_id', $enrollment->course_id)->first();
                    $course = Course::find($request->course_id);
                    if (!empty($zoom)) {
                        if ($zoom->checkstatus == 1) {
                            return response()->json([
                                'success'               =>  true,
                                'data'                  =>  $zoom,
                                'message'               => "Meeting is created for subject " . $course->name,
                            ], 200);
                        } else if ($zoom->checkstatus == 2) {
                            return response()->json([
                                'success'               =>  true,
                                'data'                  =>  $zoom,
                                'message'               => "Meeting is updated for subject " . $course->name,
                            ], 200);
                        } else if ($zoom->checkstatus == 0) {
                            return response()->json([
                                'success'               =>  true,
                                'data'                  =>  $zoom,
                                'message'               => "Meeting is canccelled for subject " . $course->name,
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'success'               =>  true,
                            'data'                  =>  "No Meeting Exists"
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  "No Zoom Meeting Announcements",
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
    public function chatHistory(Request $request)
    {
        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);


        if ($user && $user->role_id == 3) {
            // $chat = DB::table('ch_messages')->where('to_id',$id)->get();
            $chats = User::join('ch_messages', 'ch_messages.from_id', 'users.id')->where('to_id', $id)
                ->groupBy('from_id')->get();
            $chattings = [];
            foreach ($chats as $chat) {
                // $chat = $chat->groupBy('from_id')->first();
                array_push($chattings, $chat);
            }

            try {
                if (!empty($chattings)) {

                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $chattings
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Chat Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }


    public function sendMessage(Request $request)
    {

        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );
        $pusher = new Pusher(
            '6996bb200a27ad5896e9',
            '08d37f58bbf8bc8d56ad',
            '1269962',
            $options
        );

        $data = ['to_id' => $request->to_id, 'from_id' => $request->from_id, 'msg' => $request->message, 'date' => strtotime(Carbon::now())];
        $pusher->trigger("my-channel", "my-event", $data);
        $req = $request->all();

        // if (Auth::guard('api')->check())
        // {
        $id = $request->from_id;
        $user = User::find($id);

        $error = (object)[
            'status' => 0,
            'message' => null
        ];




        $attachment = null;
        $attachment_title = null;

        // if there is attachment [file]
        if ($request->hasFile('file')) {
            // allowed extensions
            $allowed_images = Chatify::getAllowedImages();
            $allowed_files  = Chatify::getAllowedFiles();
            $allowed        = array_merge($allowed_images, $allowed_files);

            $file = $request->file('file');
            // if size less than 150MB
            if ($file->getSize() < 150000000) {
                if (in_array($file->getClientOriginalExtension(), $allowed)) {
                    // get attachment name
                    $attachment_title = $file->getClientOriginalName();
                    // upload attachment and store the new name
                    $attachment = Str::uuid() . "." . $file->getClientOriginalExtension();
                    $file->storeAs("public/" . config('chatify.attachments.folder'), $attachment);
                } else {
                    $error->status = 1;
                    $error->message = "File extension not allowed!";
                }
            } else {
                $error->status = 1;
                $error->message = "File extension not allowed!";
            }
        }



        if ($user) {

            try {

                if (!$error->status) {
                    $messageID = mt_rand(9, 999999999) + time();

                    Chatify::newMessage([
                        'id' => $messageID,
                        'type' => "User",
                        'from_id' => $id,
                        'to_id' => $request->to_id,
                        'body' => htmlentities(trim($request->message), ENT_QUOTES, 'UTF-8'),
                        'attachment' => ($attachment) ? json_encode((object)[
                            'new_name' => $attachment,
                            'old_name' => htmlentities(trim($attachment_title), ENT_QUOTES, 'UTF-8'),
                        ]) : null,
                    ]);

                    //$chatify_new_message = DB::table('ch_messages')->where('from_id',$id)->where('id',$messageID)->latest()->first();

                    // fetch message to send it with the response
                    return response()->json(Chatify::fetchMessage($messageID));
                    $messageData = Chatify::fetchMessage($messageID);

                    // send to user using pusher
                    Chatify::push('private-chatify', 'messaging', [
                        'from_id' => $id,
                        'to_id' => $request->to_id,
                        'message' => Chatify::messageCard($messageData, 'default')
                    ]);



                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  "Message Sent Successfully"
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  true,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
    public function oneToOneChat(Request $request)
    {

        // if (Auth::guard('api')->check())
        // {
        $id = $request->id;
        $user = User::find($id);


        if ($user && $user->role_id == 3) {
            // $chat = DB::table('ch_messages')->where('to_id',$id)->get();
            $chats = DB::table('ch_messages')->where('to_id', $id)->orWhere('from_id', $id)->orderBy('created_at', 'ASC')->get();

            try {
                if (!empty($chats)) {

                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $chats
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Quiz Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
    public function totalQuizGiven(Request $request)
    {

        $id = $request->id;
        $user = User::find($id);


        if ($user && $user->role_id == 3) {


            $quizes = QuizSubmission::join('quizzes', 'quizzes.id', 'quiz_submissions.quiz_id')->where('quiz_submissions.student_id', $id)->get();



            try {
                if (count($quizes) > 0) {

                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  $quizes
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Quiz Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }
    public function averageQuizScore(Request $request)
    {

        $id = $request->id;
        $user = User::find($id);


        if ($user && $user->role_id == 3) {

            $sum = 0;
            $quizes = QuizSubmission::where('student_id', $id)->get();
            foreach ($quizes as $quiz) {
                $sum = $sum + $quiz->marks;
            }
            $average = 0;
            $quiz_count = count($quizes);
            if ($quiz_count == 0) {
                $average = 0;
            } else {
                $average = $sum / $quiz_count;
            }


            try {
                if (!empty($average)) {

                    return response()->json([
                        'success'               =>  true,
                        'data'                  =>  round($average, 2)
                    ], 200);
                } else {
                    return response()->json([
                        'error'             =>  true,
                        'message'           =>  'No Quiz Available'
                    ], 200);
                }
            } catch (Exception $e) {
                return response()->json([
                    'error'             =>  $e,
                    'message'           =>  'Something444 went wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'error'             =>  true,
                'message'           =>  'please login to continue'
            ], 200);
        }
    }

    public function topCategory()
    {
        $course_top_cat = DB::select('SELECT courses.id,courses.name,courses.category_id,categories.id,categories.name,COUNT(courses.category_id) as count_category from categories LEFT JOIN courses ON courses.category_id=categories.id WHERE courses.id IS NOT NULL GROUP BY (categories.id) ORDER BY count_category DESC LIMIT 5');
        if (!empty($course_top_cat)) {
            return response()->json(['success' => true, 'data' => $course_top_cat]);
        } else {
            return response()->json(['error' => true, 'message' => 'Not Top Courses Found']);
        }
    }

    public function ResultQuizMarks(Request $request)
    {
        $data = QuizSubmission::where(['student_id' => $request->user_id, 'quiz_id' => $request->quiz_id, 'course_id' => $request->course_id, 'section_id' => $request->section_id])->first();
        if (isset($data)) {
            return response()->json([
                'success' => true,
                'data' => round(($data->marks / 10), 1),
            ]);
        } else {
            return response()->json([
                'error' => true,
                'data' => 'No Quiz Result Found',
            ]);
        }
    }

    public function CategoryFilter(Request $request)
    {
        $category_courses = Course::with(['CourseDescription', 'CourseReview'])->where('category_id', '=', $request->category_id)->get();
        if (!empty($category_courses)) {
            return response()->json([
                'success' => true,
                'data' => $category_courses,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found On This Category",
            ]);
        }
    }

    public function PopularFilter(Request $request)
    {
        $popular_courses = Course::with(['CourseDescription', 'CourseReview'])->orderBy($request->popular_type, 'DESC')->get();
        if (!empty($popular_courses)) {
            return response()->json([
                'success' => true,
                'data' => $popular_courses,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found By this popular",
            ]);
        }
    }

    public function SortFilter(Request $request)
    {
        if ($request->sort_type == "name") {
            $sort_courses = Course::with(['CourseDescription', 'CourseReview'])->orderBy($request->sort_type, 'ASC')->get();
        } else {
            $sort_courses = Course::with(['CourseDescription', 'CourseReview'])->orderBy($request->sort_type, 'DESC')->get();
        }
        if (!empty($sort_courses)) {
            return response()->json([
                'success' => true,
                'data' => $sort_courses,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found By this sort",
            ]);
        }
    }

    public function AllFilters(Request $request)
    {
        if ($request->sort_type == "name") {
            $all_filter_courses = Course::with(['CourseDescription', 'CourseReview'])->where('category_id', '=', $request->category_id)->orderBy($request->popular_type, 'DESC')->orderBy($request->sort_type, 'ASC')->get();
        } else {
            $all_filter_courses = Course::with(['CourseDescription', 'CourseReview'])->where('category_id', '=', $request->category_id)->orderBy($request->popular_type, 'DESC')->orderBy($request->sort_type, 'DESC')->get();
        }
        if (!empty($all_filter_courses)) {
            return response()->json([
                'success' => true,
                'data' => $all_filter_courses,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found By all filters",
            ]);
        }
    }

    public function CategoryPopularFilter(Request $request)
    {
        $category_popular_filter = Course::with(['CourseDescription', 'CourseReview'])->where('category_id', '=', $request->category_id)->orderBy($request->popular_type, 'DESC')->get();
        if (!empty($category_popular_filter)) {
            return response()->json([
                'success' => true,
                'data' => $category_popular_filter,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found By filters",
            ]);
        }
    }
    public function PopularSortFilter(Request $request)
    {
        if ($request->sort_type == "name") {
            $popular_sort_filter = Course::with(['CourseDescription', 'CourseReview'])->orderBy($request->popular_type, 'DESC')->orderBy($request->sort_type, 'ASC')->get();
        } else {
            $popular_sort_filter = Course::with(['CourseDescription', 'CourseReview'])->orderBy($request->popular_type, 'DESC')->orderBy($request->sort_type, 'DESC')->get();
        }
        if (!empty($popular_sort_filter)) {
            return response()->json([
                'success' => true,
                'data' => $popular_sort_filter,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found By filters",
            ]);
        }
    }
    public function CategorySortFilter(Request $request)
    {
        if ($request->sort_type == "name") {
            $category_sort_filter = Course::with(['CourseDescription', 'CourseReview'])->where('category_id', '=', $request->category_id)->orderBy($request->sort_type, 'ASC')->get();
        } else {
            $category_sort_filter = Course::with(['CourseDescription', 'CourseReview'])->where('category_id', '=', $request->category_id)->orderBy($request->sort_type, 'DESC')->get();
        }
        if (!empty($category_sort_filter)) {
            return response()->json([
                'success' => true,
                'data' => $category_sort_filter,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Courses Found By all filters",
            ]);
        }
    }


    public function settings(Request $request)
    {
        $setting1 = AppSetting::all();
        $setting = $setting1[0];
        if (!empty($setting)) {
            return response()->json([
                'success' => true,
                'data' => $setting,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => "No Settings",
            ]);
        }
    }
	
public function courseStart(Request $request)
    {

        $start = new CourseStart();
        $start->course_id = $request->course_id;
		$start->student_id = $request->student_id;
        $start->start = 1;
        $start->save();


        return response()->json([
            'success' => true,
            'data' => $start,
            'message' => "Course Has Started"
        ]);
    }
	public function courseStartCheck(Request $request)
    {


        $start = CourseStart::where('course_id', $request->course_id)->where('student_id',$request->student_id)->first();
		if($start != null){
        if ($start->start == 1) {
            return response()->json([
                'success' => true,
                'data' => "start",
            ]);
        }
		}else {
            return response()->json([
                'success' => true,
                'data' => "not start"
            ]);
        }
    }
	

	public function giveRating(Request $request)
    {

        $rating = new CourseRating();
        $rating->course_id = $request->course_id;
        $rating->student_id = $request->student_id;
        $rating->count = $request->count;
        $rating->comment = $request->comment;

        $rating->save();


        return response()->json([
            'success' => true,

            'message' => "Review Submitted Successfully"
        ]);
    }


public function ratingByNumber(Request $request)
    {
        $user = User::find($request->student_id);

        $onestarcount = 0;
        $twostarcount = 0;
        $threestarcount = 0;
        $fourstarcount = 0;
        $fivestarcount = 0;

        $oneavgrating = 0;
        $twoavgrating = 0;
        $threeavgrating = 0;
        $fouravgrating = 0;
        $fiveavgrating = 0;

        if ($user && $user->role_id == 3) {

            $one = CourseRating::where('student_id', $user->id)
                ->where('course_id', $request->course_id)
                ->where('count', 1)->get();
            $two = CourseRating::where('student_id', $user->id)
                ->where('course_id', $request->course_id)
                ->where('count', 2)->get();
            $three = CourseRating::where('student_id', $user->id)
                ->where('course_id', $request->course_id)
                ->where('count', 3)->get();
            $four = CourseRating::where('student_id', $user->id)
                ->where('course_id', $request->course_id)
                ->where('count', 4)->get();
            $five = CourseRating::where('student_id', $user->id)
                ->where('course_id', $request->course_id)
                ->where('count', 5)->get();

      


            $total_sum = count($one) + count($two) + count($three) + count($four) + count($five);
            $sum1 = 0;
            $sum2 = 0;
            $sum3 = 0;
            $sum4 = 0;
            $sum5 = 0;






            $data = [];

            if (count($one) > 0) {
                foreach ($one as $o) {
                    $sum1 = $sum1 + $o->count;
                }
                $oneavgrating = (count($one) / $total_sum) * 100;
                array_push($data, "one_" . count($one) . "_" . $oneavgrating);
            } else {
                array_push($data, "one_" . $onestarcount . "_" . $oneavgrating);
            }

            if (count($two) > 0) {
                foreach ($two as $o) {
                    $sum2 = $sum2 + $o->count;
                }
                $twoavgrating = (count($two) / $total_sum) * 100;
                array_push($data, "two_" . count($two) . "_" . $twoavgrating);
            } else {
                array_push($data, "two_" . $twostarcount . "_" . $twoavgrating);
            }

            if (count($three) > 0) {
                foreach ($three as $o) {
                    $sum3 = $sum3 + $o->count;
                }
                $threeavgrating = (count($three) / $total_sum) * 100;
                array_push($data, "three_" . count($three) . "_" . $threeavgrating);
            } else {
                array_push($data, "three_" . $threestarcount . "_" . $threeavgrating);
            }

            if (count($four) > 0) {
                foreach ($four as $o) {
                    $sum4 = $sum4 + $o->count;
                }
                $fouravgrating = (count($four) / $total_sum) * 100;
                array_push($data, "four_" . count($four) . "_" . $fouravgrating);
            } else {
                array_push($data, "four_" . $fourstarcount . "_" . $fouravgrating);
            }


            if (count($five) > 0) {
                foreach ($five as $o) {
                    $sum5 = $sum5 + $o->count;
                }
                $fiveavgrating = (count($five) / $total_sum) * 100;
                array_push($data, "five_" . count($five) . "_" . $fiveavgrating);
            } else {
                array_push($data, "five_" . $fivestarcount . "_" . $fiveavgrating);
            }



            return response()->json([
                'success' => true,
                'data' => $data,

            ]);
        } else {
            return response()->json([
                'error' => true,
                'data' => "sfg",

            ]);
        }
    }

    public function averageRating(Request $request)
    {
        $user = User::find($request->student_id);

        $average_rating = 0;
        if ($user && $user->role_id == 3) {
            $ratings = CourseRating::where('student_id', $user->id)
                ->where('course_id', $request->course_id)
                ->get();
            $sum = 0;
            if (count($ratings) > 0) {
                foreach ($ratings as $r) {
                    $sum = $sum + $r->count;
                }
                $average_rating = $sum / count($ratings);

                return response()->json([
                    'success' => true,
                    'data' => $average_rating,
					'num_reviews'=>count($ratings),
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => $average_rating,
                ]);
            }
        }
    }
	public function lessonSubmission(Request $request)
    {

        $submit = StudentLesson::where('section_id', $request->section_id)
            ->where('lesson_id', $request->lesson_id)
            ->where('student_id', $request->student_id)
			->where('course_id', $request->course_id)
            ->first();
        if(empty($submit)){
            $submit = new StudentLesson();
            $submit->section_id = $request->section_id;
            $submit->lesson_id = $request->lesson_id;
            $submit->student_id = $request->student_id;
			$submit->course_id = $request->course_id;

            $submit->status = 1;
            $submit->save();
            return response()->json([
                'success' => true,
                'data' => "Lesson submitted successfully",
            ]);
        }else{
            return response()->json([
                'success' => true,
                'data' => "Lesson Already submitted",
            ]);
        }   
    }
	public function lessonSubmissionCheck(Request $request)
    {

        $submit = StudentLesson::where('section_id', $request->section_id)
            ->where('lesson_id', $request->lesson_id)
            ->where('student_id', $request->student_id)
			->where('course_id', $request->course_id)
            ->first();
        if(!empty($submit)){
            return response()->json([
                'success' => true,
                'data' => "Lesson Already submitted",
            ]);
        }else{
            return response()->json([
                'success' => true,
                'data' => "Submit Lesson",
            ]);
        }   
    }
	public function downloadFile(Request $request){

        $file = CourseFile::where('course_id',$request->course_id)->first();
        if (!empty($file)) {
            if (file_exists(Storage_path('app/public/' . $file->file))) {
                $filePath = Storage_path('app/public/' . $file->file);
                $fileExt = explode('.', $file->file);
                $fileName = time() . '.' . end($fileExt);

                return response()->download($filePath, $fileName);
            } else {
                return response()->json([
                    'error' => 'File does not exits'
                ]);
            }
        } else {
            return response()->json([
                    'error' => 'File is empty'
                ]);
        }
    }
	

	public function banners(Request $request)
		{

			$banners = Banner::all();
			if (count($banners) > 0) {

				return response()->json([
					'success' => true,
					'data' => $banners
				]);
			} else {
				return response()->json([
					'success' => true,
					'data' => "No Banner Available"
				]);
			}
		}
}
