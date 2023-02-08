<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseReview extends Model
{
    Public function User(){
        return $this->belongsTo('App\User')->withDefault();
    }

    Public function Course(){
        return $this->belongsTo('App\Course')->withDefault();
    }
}
