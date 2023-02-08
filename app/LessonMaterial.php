<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonMaterial extends Model
{
    protected $fillable = [
        'file',
        'type',
        'file_title',
        'lesson_id'
    ];

    public function Lesson() {
        return $this->belongsTo('App\Lesson')->withDefault();
    }
}
