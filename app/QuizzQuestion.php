<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizzQuestion extends Model
{
    use HasFactory;

    protected $table = 'q_questions';
}
