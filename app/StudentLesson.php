<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLesson extends Model
{
    protected $fillable = ['lesson_id','student_id','section_id','status'];
    use HasFactory;
}
