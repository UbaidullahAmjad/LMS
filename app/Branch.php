<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name'
    ];
     
    Public function Course(){
        return $this->hasMany('App\Course')->withDefault();
    }

    Public function User(){
        return $this->hasMany('App\User')->withDefault();
    }

}
