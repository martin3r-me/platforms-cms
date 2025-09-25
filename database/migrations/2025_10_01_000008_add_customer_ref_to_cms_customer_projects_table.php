<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_customer_projects', function (Blueprint $table) {
            // Polymorph-ähnliche Referenz: Modul/Modell-Key + ID
            $table->string('customer_model')->nullable()->after('company_id'); // z.B. 'crm.companies' oder 'crm.contacts'
            $table->unsignedBigInteger('customer_id')->nullable()->after('customer_model');
            $table->index(['customer_model', 'customer_id'], 'cms_cust_proj_model_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cms_customer_projects', function (Blueprint $table) {
            if (Schema::hasColumn('cms_customer_projects', 'customer_id')) {
                $table->dropIndex('cms_cust_proj_model_id_idx');
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('cms_customer_projects', 'customer_model')) {
                $table->dropColumn('customer_model');
            }
        });
    }
};

 

