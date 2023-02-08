<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentAssignment extends Model
{
    protected $fillable = [
        'assignment_id',
        'file',
        'file_type',
        'status',
        'user_id',
        'message',
        'marks'
    ];

    Public function User(){
        return $this->belongsTo('\App\User')->withDefault();
    }
}
