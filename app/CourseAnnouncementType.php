<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseAnnouncementType extends Model
{
    protected $fillable = [
        'title',
        'course_id'
    ]; 

    Public function CourseAnnouncement(){
        return $this->hasMany('App\CourseAnnouncement');
    }
}
