<?php

namespace App\Http\Controllers;

use App\Models\Avatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function index(){
        $avatars=Avatar::all();
        return view('dashboard.avatar',['avatars'=>$avatars]);
    }
    public function store(Request $request){
        $atts=$request->validate([
            'avatar'=>['image']
        ]);
        if($request->hasFile('avatar')){
            $avatar=Storage::disk('public')->put('/',$request->file('avatar'));
            Avatar::create(['file_name'=>$avatar]);
        }
        return redirect('/avatar');
    }
    public function destroy(Avatar $avatar){
        Storage::disk('public')->delete('/'.$avatar->file_name);
        $avatar->delete();
        return redirect('/avatar');
    }
}
