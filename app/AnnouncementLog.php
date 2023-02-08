<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementLog extends Model
{
    use HasFactory;
    protected $fillable=['announcement_id','sent_from','sent_to','is_received','is_read'];

    public function announcements()
{
    return $this->belongsTo(Announcement::class,'announcement_id');
}

}
