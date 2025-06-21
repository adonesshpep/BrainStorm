<?php

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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('nanoid',12)->unique();
            $table->string('name')->unique();
            $table->index('name');
            $table->index('nanoid');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('size')->default(false);
            $table->foreignIdFor(User::class,'admin_id')->constrained('users')->onDelete('cascade');
            $table->boolean('private')->default(false);
            $table->boolean('joining_requires_admin_approval')->default(false);
            $table->boolean('puzzles_require_admin_approval')->default(false);
            $table->boolean('only_admin_can_post')->default(false);
            $table->timestamps();
        });
        Schema::create('community_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Community::class)->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_user');
        Schema::dropIfExists('communities');
    }
};
