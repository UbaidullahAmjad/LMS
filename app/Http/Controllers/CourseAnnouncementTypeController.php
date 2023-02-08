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
class CourseAnnouncementTypeController extends Controller
{

        // begin store announcement type

        public function store(Request $request)
        {
            
            $addCourseAnnouncementType                                          =           new CourseAnnouncementType;

            $addCourseAnnouncementType->title                                   =           $request->title;
            $addCourseAnnouncementType->course_id                               =           $request->course;
            
            

            if($addCourseAnnouncementType->save())
            {
                return redirect()->back()->with('success','Course Announcement Type Added successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course Announcement Type cannot be added');
            }
        }
        // end store announcement type

        // begin delete announcement type
        public function delete($id)
        {
            if(CourseAnnouncementType::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Course announcement type deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course announcement type cannot be deleted');
            }

        }
        // end delete announcement type
        
}
