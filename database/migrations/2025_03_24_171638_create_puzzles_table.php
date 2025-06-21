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
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Category::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Community::class)->nullable()->constrained()->onDelete('cascade');
            $table->index('category_id');
            $table->index('community_id');
            $table->string('question');
            $table->boolean('status')->default(false);
            $table->string('image_path')->nullable();
            $table->integer('level')->check('level IN (0, 1, 2)');
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
