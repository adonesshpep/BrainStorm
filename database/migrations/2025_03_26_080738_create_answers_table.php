<?php

use App\Models\Puzzle;
use App\Models\Solution;
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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Puzzle::class)->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->foreignIdFor(Solution::class)->nullable()->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('answer')->nullable();
            $table->boolean('iscorrect');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
