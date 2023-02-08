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
class CourseDescriptionController extends Controller
{

        // begin store course description
        public function store(Request $request)
        {
            
            $addCourseDescription                  =           new CourseDescription;

            $addCourseDescription->heading         =           $request->heading;
            $addCourseDescription->description     =           $request->description;
            if( $request->hasFile('image')) 
            {
                $image                      =   $request->file('image');
                $path                       =   public_path(). '/course_description_images';
                $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
                if($image->move($path, $filename))
                {
                    $addCourseDescription->image                 =   $filename;
                }
                else
                {
                    $addCourseDescription->image                 =   '';
                }
    
            }
            else
            {
                $addCourseDescription->image                 =   '';
            }
            $addCourseDescription->course_id       =           $request->course;
            

            if($addCourseDescription->save())
            {
                return redirect()->back()->with('success','Course Description Added successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course Description cannot be added');
            }
        }
        // end store course description
        
        // begin delete course description
        public function delete($id)
        {
            if(CourseDescription::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Course description deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Course description cannot be deleted');
            }

        }
        // end delete course description
}
