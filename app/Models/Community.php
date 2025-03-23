<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;
    public function users(){
        return $this->belongsToMany(User::class)->withPivotValue('isadmin',False);
    }
    public function admins(){
        return $this->belongsToMany(User::class)->withPivotValue('isadmin',True); 
    }
    public function categories(){
        return $this->belongsToMany(Category::class);
    }
    
}
