<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Puzzle extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($puzzle) {
            if ($puzzle->image_path && Storage::disk('public')->exists($puzzle->image_path)) {
                Storage::disk('public')->delete($puzzle->image_path);
            }
        });
    }
    protected $guarded=['votes_up','votes_down'];
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
