<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'image',
        'category_id',
        'sub_category_id',
        'user_id',
        'branch_id',
        'total_reviews',
        'total_ratings',
        'total_enrolled',
        'total_duration',
        'total_lectures',
        'total_videos',
        'level',
        'fee',
        'teacher_id'
    ]; 
    
    Public function Category(){
        return $this->belongsTo('App\Category')->withDefault();
    }

    Public function SubCategory(){
        return $this->belongsTo('App\Category','parent_id')->withDefault();
    }

    Public function User(){
        return $this->belongsTo('App\User')->withDefault();
    }

    Public function Branch(){
        return $this->belongsTo('App\Branch')->withDefault();
    }


    Public function CourseAnnouncementType(){
        return $this->hasMany('App\CourseAnnouncementType');
    }


    Public function CourseCurriculumType(){
        return $this->hasMany('App\CourseCurriculumType');
    }


    Public function CourseDescription(){
        return $this->hasMany('App\CourseDescription');
    }

    Public function CourseFaq(){
        return $this->hasMany('App\CourseFaq');
    }

    Public function CourseReview(){
        return $this->hasMany('App\CourseReview');
    }

    Public function CourseWorkingHour(){
        return $this->hasMany('App\CourseWorkingHour');
    }

    Public function Lesson(){
        return $this->hasMany('App\Lesson');
    }

}
