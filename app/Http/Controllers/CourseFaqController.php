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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CourseFaqController extends Controller
{

        // begin store faq

            public function store(Request $request)
            {
                
                $addFaq                  =           new CourseFaq;

                $addFaq->heading         =           $request->heading;
                $addFaq->description     =           $request->description;
                $addFaq->course_id       =           $request->course;
                

                if($addFaq->save())
                {
                    return redirect()->back()->with('success','Course FAQ Added successfully');
                }
                else
                {
                    return redirect()->back()->with('error','Course FAQ cannot be added');
                }
            }
        // end store faq

        // begin delete faq
        public function delete($id)
        {
            if(CourseFaq::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Course Faq deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course Faq cannot be deleted');
            }

        }
        // end delete faq
        
}
