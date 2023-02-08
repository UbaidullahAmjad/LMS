<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseCurriculumType extends Model
{
    protected $fillable = [
        'title',
        'course_id'
    ]; 

    Public function CourseCurriculum(){
        return $this->hasMany('App\CourseCurriculum');
    }
}
