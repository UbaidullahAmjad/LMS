<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementHistory extends Model
{
    use HasFactory;
    protected $fillable=['sending_date','sending_time','group_id','announcement_id'];

    public function groups()
    {
        return $this->belongsTo(Group::class , 'group_id');
    }
    public function announcements()
    {
        return $this->belongsTo(Announcement::class , 'announcement_id');
    }
}
