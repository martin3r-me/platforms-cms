<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_customer_projects', function (Blueprint $table) {
            $table->string('customer_tool')->nullable()->after('customer_id'); // z.B. 'crm.open'
            $table->string('customer_url')->nullable()->after('customer_tool');
        });
    }

    public function down(): void
    {
        Schema::table('cms_customer_projects', function (Blueprint $table) {
            if (Schema::hasColumn('cms_customer_projects', 'customer_url')) {
                $table->dropColumn('customer_url');
            }
            if (Schema::hasColumn('cms_customer_projects', 'customer_tool')) {
                $table->dropColumn('customer_tool');
            }
        });
    }
};

 

