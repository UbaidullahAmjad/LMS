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
class CourseAnnouncementController extends Controller
{

        // begin store announcement

        public function store(Request $request)
        {
            
            $addannouncement                                            =           new CourseAnnouncement;

            $addannouncement->heading                                   =           $request->heading;
            $addannouncement->description                               =           $request->description;
            $addannouncement->course_announcement_type_id               =           $request->Announcement_Type;
            if($addannouncement->save())
            {
                return redirect()->back()->with('success','Course Announcement Added successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course Announcement cannot be added');
            }
        }
        // end store announcement

        // begin delete announcement
        public function delete($id)
        {
            if(CourseAnnouncement::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Course Announcement deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course Announcement cannot be deleted');
            }

        }
        // end delete announcement
        
}
