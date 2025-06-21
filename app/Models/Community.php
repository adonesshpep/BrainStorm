<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function users() {
        return $this->belongsToMany(User::class);
    }

    // public function admins() {
    //     return $this->belongsToMany(User::class)
    //                 ->withPivot('isadmin')
    //                 ->wherePivot('isadmin', true);
    // }
    public function puzzles(){
        return $this->hasMany(Puzzle::class);
    }
    public function categories() {
        return $this->belongsToMany(Category::class);
    }
    public function joinRequests(){
        return $this->belongsToMany(User::class,'community_join_requests','community_id','user_id');
    }
}
