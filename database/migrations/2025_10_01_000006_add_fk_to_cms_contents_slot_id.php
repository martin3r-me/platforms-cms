<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_board_slots') && Schema::hasTable('cms_contents')) {
            Schema::table('cms_contents', function (Blueprint $table) {
                $table->foreign('slot_id')->references('id')->on('cms_board_slots')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('cms_contents', function (Blueprint $table) {
            $table->dropForeign(['slot_id']);
        });
    }
};


