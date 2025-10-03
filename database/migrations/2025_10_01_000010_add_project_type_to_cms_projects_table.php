<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_projects', function (Blueprint $table) {
            $table->string('project_type')->nullable()->default('internal')->after('team_id');
        });
    }

    public function down(): void
    {
        Schema::table('cms_projects', function (Blueprint $table) {
            if (Schema::hasColumn('cms_projects', 'project_type')) {
                $table->dropColumn('project_type');
            }
        });
    }
};

 

