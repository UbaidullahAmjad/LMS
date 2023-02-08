<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'id',
        'name',
        'slug',
        'image',
        'icon',
        'parent_id'
    ]; 

    Public function public(){
        return $this->belongsTo('App\Category', 'parent_id', 'id')->withDefault();
    }

    Public function Course(){
        return $this->hasMany('App\Course','category_id')->withDefault();
    }
}
