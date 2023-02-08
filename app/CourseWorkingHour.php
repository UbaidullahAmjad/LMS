<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseWorkingHour extends Model
{
    protected $fillable = [
        'day1',
        'day2',
        'day3',
        'day4',
        'day5',
        'day6',
        'day7',
        'couser_id'
    ];  

    Public function Course(){
        return $this->belongsTo('App\Course')->withDefault();
    }
}
