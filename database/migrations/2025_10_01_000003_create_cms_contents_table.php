<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->nullOnDelete();
            $table->foreignId('board_id')->nullable()->constrained('cms_boards')->nullOnDelete();
            // Slot wird in späterer Migration mit FK versehen (Tabelle muss existieren)
            $table->foreignId('slot_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->json('meta')->nullable();
            $table->string('status')->default('draft'); // draft|review|published|archived
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_contents');
    }
};


