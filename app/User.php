<?php

namespace App;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'status',
        'image',
        'phone',
        'role_id',
        'google_id',
        'country_id',
        'branch_id',
        'address',
        'state_id',
        'city_id',
        'zip_code',
        'detail',
        'facebook_url',
        'youtube_url',
        'twitter_url',
        'linkedIn_url',
        'remarks'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    Public function Role(){
        return $this->belongsTo('\App\Role')->withDefault();
    }

    Public function Course(){
        return $this->hasMany('App\Course');
    }

	Public function City(){
        return $this->belongsTo('\App\City')->withDefault();
    }

    Public function State(){
        return $this->belongsTo('\App\State')->withDefault();
    }

    Public function Country(){
        return $this->belongsTo('\App\Country')->withDefault();
    }

    public function TeacherDocument() {
        return $this->hasMany('\App\TeacherDocument');
    }

    Public function Branch(){
        return $this->belongsTo('\App\Branch')->withDefault();
    }
}
