<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'lesson_id',
        'lessons_id',
        'title',
        'image',
        'description',
        'attempt_marks',
        'status'
    ];

    Public function Lesson(){
        return $this->belongsTo('\App\Lesson')->withDefault();
    }
}
