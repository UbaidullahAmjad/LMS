<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSubmission extends Model
{
    protected $fillable =['quiz_id','course_id','student_id','section_id','marks','time'];
    use HasFactory;
}
