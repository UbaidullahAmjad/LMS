<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    protected $fillable = [
        'name'
    ];

    Public function QuestionBankQuestion(){
        return $this->hasMany('App\QuizQuestion');
    }
}
