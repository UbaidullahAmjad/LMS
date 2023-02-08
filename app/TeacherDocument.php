<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeacherDocument extends Model
{
    protected $fillable = [
        //'name',
        'file',
        //'type',
        'user_id'
    ];

    Public function User(){
        return $this->belongsTo('\App\User')->withDefault();
    }
}
