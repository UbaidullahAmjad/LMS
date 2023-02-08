<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'option1',
        'option2',
        'option3',
        'option4',
        'option5',
        'answer',
        'quiz_id',
        'question_bank_id'
    ];

    public function Quiz() {
        return $this->belongsTo('App\Quiz')->withDefault();
    }


}
