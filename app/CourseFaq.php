<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseFaq extends Model
{
    protected $fillable = [
        'heading',
        'description',
        'course_id'
    ]; 

    Public function Course(){
        return $this->belongsTo('App\Course')->withDefault();
    }
}
