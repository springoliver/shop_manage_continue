<?php

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
        if (!Schema::hasTable('stoma_catalog_products')) {
            Schema::create('stoma_catalog_products', function (Blueprint $table) {
                $table->increments('catalog_product_id');
                $table->string('catalog_product_name', 155);
                $table->string('catalog_product_price', 55);
                $table->string('catalog_product_desc', 555);
                $table->integer('storeid');
                $table->integer('catalog_product_groupid');
                $table->integer('catalog_product_categoryid');
                $table->enum('catalog_product_status', ['Enable', 'Disable'])->default('Enable');
                $table->string('income_sum', 10)->nullable();
                $table->string('profit_percentage', 10)->default('');
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
                $table->timestamp('editdate')->nullable();
                $table->integer('editip')->nullable();
            });
        }

        if (!Schema::hasTable('stoma_catalog_product_ingredients')) {
            Schema::create('stoma_catalog_product_ingredients', function (Blueprint $table) {
                $table->increments('catalog_product_ingredient_id');
                $table->integer('storeid');
                $table->integer('store_product_id');
                $table->integer('catalog_product_id');
                $table->integer('percentage');
                $table->string('price', 30);
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
            });
        }

        if (!Schema::hasTable('stoma_catalog_products_settings')) {
            Schema::create('stoma_catalog_products_settings', function (Blueprint $table) {
                $table->increments('settingid');
                $table->string('title', 255);
                $table->integer('storeid');
                $table->text('value');
            });
        }

        if (!Schema::hasTable('stoma_catalog_products_addons')) {
            Schema::create('stoma_catalog_products_addons', function (Blueprint $table) {
                $table->increments('addonid');
                $table->integer('storeid');
                $table->string('addon', 55);
                $table->smallInteger('price');
                $table->integer('product_categoryid');
                $table->integer('product_groupid');
                $table->enum('addon_status', ['Enable', 'Disable'])->default('Enable');
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
                $table->timestamp('editdate')->nullable();
                $table->string('editip', 51)->nullable();
            });
        }

        if (!Schema::hasTable('stoma_catalog_products_modifiers')) {
            Schema::create('stoma_catalog_products_modifiers', function (Blueprint $table) {
                $table->increments('modifier_id');
                $table->string('modifier_name', 155);
                $table->string('modifier_price', 55);
                $table->integer('storeid');
                $table->enum('modifier_status', ['Enable', 'Disable'])->default('Enable');
                $table->string('income_sum', 10)->nullable();
                $table->string('profit_percentage', 10)->default('');
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
                $table->timestamp('editdate')->nullable();
                $table->integer('editip')->nullable();
            });
        }

        if (!Schema::hasTable('stoma_catalog_products_payment_methods')) {
            Schema::create('stoma_catalog_products_payment_methods', function (Blueprint $table) {
                $table->increments('payment_methodid');
                $table->integer('storeid');
                $table->string('payment_method', 55);
                $table->string('email', 55);
                $table->integer('merchantid');
                $table->string('currency', 11);
                $table->enum('mode', ['Live Mode', 'Test Mode']);
                $table->enum('status', ['Active', 'Inactive'])->default('Active');
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
                $table->timestamp('editdate')->nullable();
                $table->string('editip', 51)->nullable();
            });
        }

        if (!Schema::hasTable('stoma_catalog_products_sold_man')) {
            Schema::create('stoma_catalog_products_sold_man', function (Blueprint $table) {
                $table->increments('catalog_products_sold_man_id');
                $table->string('catalog_product_id', 155);
                $table->date('sold_entry_date_from');
                $table->date('sold_entry_date_to');
                $table->string('catalog_product_sold_count', 555);
                $table->integer('storeid');
                $table->integer('catalog_product_categoryid');
                $table->integer('catalog_product_groupid');
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
                $table->timestamp('editdate')->nullable();
                $table->integer('editip')->nullable();
            });
        }

        if (!Schema::hasTable('stoma_catalog_products_stock_man')) {
            Schema::create('stoma_catalog_products_stock_man', function (Blueprint $table) {
                $table->increments('catalog_products_stock_man_id');
                $table->string('productid', 155);
                $table->date('inventory_entry_date');
                $table->string('catalog_product_current_stock_level', 555);
                $table->integer('storeid');
                $table->integer('catalog_product_groupid');
                $table->integer('catalog_product_categoryid');
                $table->dateTime('insertdate');
                $table->string('insertip', 51);
                $table->string('insertby', 51);
                $table->timestamp('editdate')->nullable();
                $table->integer('editip')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoma_catalog_products_stock_man');
        Schema::dropIfExists('stoma_catalog_products_sold_man');
        Schema::dropIfExists('stoma_catalog_products_payment_methods');
        Schema::dropIfExists('stoma_catalog_products_modifiers');
        Schema::dropIfExists('stoma_catalog_products_addons');
        Schema::dropIfExists('stoma_catalog_products_settings');
        Schema::dropIfExists('stoma_catalog_product_ingredients');
        Schema::dropIfExists('stoma_catalog_products');
    }
};

