<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    protected $fillable = [
        'sent_from',
        'sent_to',
        'is_read'
    ]; 

    Public function SentFrom(){
        return $this->belongsTo('\App\User','id','sent_from')->withDefault();
    }

    Public function SentTo(){
        return $this->belongsTo('\App\User','id','sent_to')->withDefault();
    }
}
