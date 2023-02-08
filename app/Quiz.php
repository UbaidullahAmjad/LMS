<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'short_description',
        'long_description',
        'passing_grade',
        'points_cut_after_re_take',
        'lesson_id',
        'lessons_id',
        'questions_id'
    ];

    public function Lesson() {
        return $this->belongsTo('App\Lesson')->withDefault();
    }

    Public function QuizQuestion(){
        return $this->hasMany('App\QuizQuestion');
    }
}
