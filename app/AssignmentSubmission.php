<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = ['detail','file','status','start_date','end_date','assignment_id','student_id','course_id','section_id'];
    use HasFactory;
}
