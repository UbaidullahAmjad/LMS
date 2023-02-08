<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class section_drip extends Model
{
    use HasFactory;

    protected $fillable = ['course_id','parent_id','child_id'];
}
