<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'title',
        'short_description',
        'long_description',
        'type',
        'duration',
        'available_to',
        'image',
        'video_url',
        'allow_comments',
        'course_id',
        'courses_id'
    ];

    public function Course() {
        return $this->belongsTo('App\Course')->withDefault();
    }

    Public function LessonMaterial(){
        return $this->hasMany('App\LessonMaterial');
    }

    Public function Quiz(){
        return $this->hasMany('App\Quiz');
    }

    public function Assignment() {
        return $this->hasMany('App\Assignment');
    }
}
