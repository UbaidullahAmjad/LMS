<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseCurriculum extends Model
{
    protected $table    =   'course_curriculums';
    protected $fillable = [
        'heading',
        'description',
        'file',
        'course_curriculum_type_id'
    ]; 
}
