<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    use HasFactory;
    protected $table="parents";
    protected $fillable=['user_id','father_name','mother_name','father_mobile_number',
    'mother_mobile_number','father_email','mother_email','father_DOB','mother_DOB','address'];
}
