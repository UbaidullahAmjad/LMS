<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementGroup extends Model
{
    use HasFactory;
    protected $fillable=['user_id','group_id'];


    public function users()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
    public function groups()
    {
        return $this->belongsTo(Group::class , 'group_id');
    }
}
