<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\CourseFaq;
use App\User;
use App\CourseAnnouncementType;
use App\CourseAnnouncement;
use App\CourseCurriculumType;
use App\CourseCurriculum;
use App\CourseDescription;
use App\CourseWorkingHour;
use App\CurriculumAssignment;
use App\CurriculumLesson;
use App\CurriculumQuiz;
use App\Section;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CourseCurriculumController extends Controller
{

        // begin store faq

            public function store(Request $request)
            {

                $addCourseCurriculum                  =           new CourseCurriculum;

                $addCourseCurriculum->heading         =           $request->heading;
                $addCourseCurriculum->description     =           $request->description;
                if( $request->hasFile('file'))
                {
                    $file                      =   $request->file('file');
                    $path                       =   public_path(). '/course_curriculum_images';
                    $filename                   =   $file->getClientOriginalName().time() . '.' . $file->getClientOriginalExtension();
                    if($file->move($path, $filename))
                    {
                        $addCourseCurriculum->file                 =   $filename;
                    }
                    else
                    {
                        $addCourseCurriculum->file                 =   '';
                    }

                }
                else
                {
                    $addCourseCurriculum->file                 =   '';
                }
                $addCourseCurriculum->course_curriculum_type_id       =           $request->Curriculum_Type;


                if($addCourseCurriculum->save())
                {
                    return redirect()->back()->with('success','Course Curriculum Added successfully');
                }
                else
                {
                    return redirect()->back()->with('error','Course Curriculum cannot be added');
                }
            }
        // end store faq


        // begin delete course curriculum
        public function delete($id)
        {
            if(CourseCurriculum::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Course Curriculum deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course Curriculum cannot be deleted');
            }

        }
        // end delete course curriculum


        public function allData(Request $request,$id){

            $request->validate([
                'name' => 'required'
            ]);
            // dd($request->all());

            $max_index = Section::where('course_id',$id)->max('index');
            $section = new Section();
            $section->course_id = $id;
            $section->name = $request->name;
            $section->index = $max_index + 1;
            $section->save();

            $curriculum = new CourseCurriculum();
            $curriculum->section_id = $section->id;
            $curriculum->course_id = $id;
            $curriculum->save();

            $j = 0;

            if(!empty($request->quizes)){
                for($i = 0; $i < count($request->quizes); $i++,$j++){
                    $c_quiz = new CurriculumQuiz();
                    $c_quiz->course_curriculum_id = $curriculum->id;
                    $c_quiz->quiz_id = $request->quizes[$i];
                    $c_quiz->section_id = $section->id;

                    $c_quiz->index = $j;

                    $c_quiz->save();
                }
            }

            if(!empty($request->assignments)){
                for($i = 0; $i < count($request->assignments); $i++,$j++){
                    $c_assignment = new CurriculumAssignment();
                    $c_assignment->course_curriculum_id = $curriculum->id;
                    $c_assignment->assignment_id = $request->assignments[$i];
                    $c_assignment->section_id = $section->id;
                    $c_assignment->index = $j;

                    $c_assignment->save();
                }
            }

            if(!empty($request->lessons)){
                for($i = 0; $i < count($request->lessons); $i++,$j++){
                    $c_lesson = new CurriculumLesson();
                    $c_lesson->course_curriculum_id = $curriculum->id;
                    $c_lesson->lesson_id = $request->lessons[$i];
                    $c_lesson->section_id = $section->id;

                    $c_lesson->index = $j;

                    $c_lesson->save();
                }
            }

            return back();


        }

        public function qdelete($id,$c_id){
			
            CurriculumQuiz::where('quiz_id',$id)->where('course_curriculum_id',$c_id)->delete();

            return back();
        }

        public function adelete($id,$c_id){
            CurriculumAssignment::where('assignment_id',$id)->where('course_curriculum_id',$c_id)->delete();

            return back();
        }

        public function ldelete($id,$c_id){
            CurriculumLesson::where('lesson_id',$id)->where('course_curriculum_id',$c_id)->delete();

            return back();
        }


        public function editSection(Request $request,$id){

            

            $section = Section::find($id);
            $section->name = $request->name;
            $section->save();
			
			$curriculum = CourseCurriculum::where('section_id',$section->id)->first();
			
			$max1 = CurriculumAssignment::max('index');
			$max2 = CurriculumQuiz::max('index');
			$max3 = CurriculumLesson::max('index');
			$max = max($max1,$max2,$max3);
			
			$j = $max + 1;
			if(!empty($request->quizess)){
                for($i = 0; $i < count($request->quizess); $i++,$j++){
                    $c_quiz = new CurriculumQuiz();
                    $c_quiz->course_curriculum_id = $curriculum->id;
                    $c_quiz->quiz_id = $request->quizess[$i];
                    $c_quiz->section_id = $section->id;

                    $c_quiz->index = $j;

                    $c_quiz->save();
                }
            }

            if(!empty($request->assignmentss)){
                for($i = 0; $i < count($request->assignmentss); $i++,$j++){
                    $c_assignment = new CurriculumAssignment();
                    $c_assignment->course_curriculum_id = $curriculum->id;
                    $c_assignment->assignment_id = $request->assignmentss[$i];
                    $c_assignment->section_id = $section->id;
                    $c_assignment->index = $j;

                    $c_assignment->save();
                }
            }

            if(!empty($request->lessonss)){
                for($i = 0; $i < count($request->lessonss); $i++,$j++){
                    $c_lesson = new CurriculumLesson();
                    $c_lesson->course_curriculum_id = $curriculum->id;
                    $c_lesson->lesson_id = $request->lessonss[$i];
                    $c_lesson->section_id = $section->id;

                    $c_lesson->index = $j;

                    $c_lesson->save();
                }
            }

            return back();
        }

        public function delSection(Request $request,$id){



            $section = Section::find($id);
            $curriculums = CourseCurriculum::where('section_id',$section->id)->get();

            foreach($curriculums as $curriculum){
                $quizes = CurriculumQuiz::
                where('course_curriculum_id',$curriculum->id)->delete();
                $assings = CurriculumAssignment::
                where('course_curriculum_id',$curriculum->id)->delete();
                $lessons = CurriculumLesson::
                where('course_curriculum_id',$curriculum->id)->delete();

                $curriculum->delete();
            }

            $section->delete();



            return back();
        }





}
