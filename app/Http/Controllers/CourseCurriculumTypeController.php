<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\CourseAnnouncement;
use App\User;
use App\CourseAnnouncementType;
use App\CourseCurriculumType;
use App\CourseCurriculum;
use App\CourseDescription;
use App\CourseWorkingHour;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CourseCurriculumTypeController extends Controller
{

        // begin store course curriculum type
        public function store(Request $request)
        {
            
            $addCourseCurriculumType                                          =           new CourseCurriculumType;

            $addCourseCurriculumType->name                                   =           $request->name;
            $addCourseCurriculumType->course_id                               =           $request->course;
            
            

            if($addCourseCurriculumType->save())
            {
                return redirect()->back()->with('success','Course curriculum Type Added successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course curriculum Type cannot be added');
            }
        }
        // end store course curriculum type 

        // begin delete course curriculum type
        public function delete($id)
        {
            if(CourseCurriculumType::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Course curriculum type deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course curriculum type cannot be deleted');
            }

        }
        // end delete course curriculum type
        
}
