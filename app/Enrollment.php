<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;
    protected $table='enrollment';
    protected $fillable=['user_id' , 'course_id'];


    public function users()
    {
        return $this->belongsTo(User::class ,'user_id');
    }

    public function courses()
    {
        return $this->belongsTo(Course::class ,'course_id');
    }

}
