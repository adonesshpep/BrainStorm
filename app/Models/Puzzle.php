<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function solutions(){
        return $this->hasMany(Solution::class);
    }
    public function answers(){
        return $this->hasMany(Answer::class);
    }
}
