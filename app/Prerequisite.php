<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prerequisite extends Model
{
    use HasFactory;
    protected $fillable=['parent_course_id','prerequisite_course_id'];
    
}
