<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded=[];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'=>'hashed'
    ];
    public function puzzles(){
        return $this->hasMany(Puzzle::class);
    }
    public function answers(){
        return $this->hasMany(Answer::class);
    }
    public function communities() {
        return $this->belongsToMany(Community::class)->withPivot('isadmin');
    }

    public function myCommunties(){
        return $this->belongsToMany(Community::class)->withPivot('isadmin')->wherePivot('isadmin', true);
    }
    public function following(){
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id');
    }
    public function followers(){
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id');
    }
    public function staredCategories(){
        return $this->belongsToMany(Category::class,'star_categories','user_id','category_id');
    }
    public function joinRequests(){
        return $this->belongsToMany(Community::class,'community_join_requests','user_id','community_id');
    }
}
