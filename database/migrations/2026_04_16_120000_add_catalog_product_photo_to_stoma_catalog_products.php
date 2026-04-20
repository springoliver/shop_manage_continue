<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stoma_catalog_products')) {
            return;
        }

        if (!Schema::hasColumn('stoma_catalog_products', 'catalog_product_photo')) {
            Schema::table('stoma_catalog_products', function (Blueprint $table) {
                $table->string('catalog_product_photo', 255)->nullable()->after('catalog_product_desc');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('stoma_catalog_products')) {
            return;
        }

        if (Schema::hasColumn('stoma_catalog_products', 'catalog_product_photo')) {
            Schema::table('stoma_catalog_products', function (Blueprint $table) {
                $table->dropColumn('catalog_product_photo');
            });
        }
    }
};

