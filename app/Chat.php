<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'sent_from',
        'sent_to',
        'message',
        'file',
        'type'
    ]; 

    Public function SentFrom(){
        return $this->belongsTo('\App\User','id','sent_from')->withDefault();
    }

    Public function SentTo(){
        return $this->belongsTo('\App\User','id','sent_to')->withDefault();
    }
}
