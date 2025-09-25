<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_contents', 'slot_id')) {
                $table->foreignId('slot_id')->nullable()->index()->after('board_id');
            }
            $table->foreign('slot_id')->references('id')->on('cms_board_slots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cms_contents', function (Blueprint $table) {
            if (Schema::hasColumn('cms_contents', 'slot_id')) {
                $table->dropForeign(['slot_id']);
                // Spalte bewusst behalten (Rollback vermeiden), nur FK entfernen
            }
        });
    }
};

 

