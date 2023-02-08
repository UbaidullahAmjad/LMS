<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseAnnouncement extends Model
{
    protected $fillable = [
        'heading',
        'description',
        'course_announcement_type_id'
    ]; 
}
