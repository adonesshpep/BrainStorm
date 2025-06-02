<?php

use App\Models\Category;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('puzzles',function(Blueprint $table){
            $table->id();
            $table->string('title');
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Category::class)->nullable();
            $table->foreignIdFor(Community::class)->nullable();
            $table->index('community_id');
            $table->string('question');
            $table->boolean('status')->default(0);
            $table->string('level');
            $table->integer('votes_up')->default(0);
            $table->integer('votes_down')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puzzles');
    }
};
