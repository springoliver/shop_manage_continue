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
        Schema::table('stoma_payment_card', function (Blueprint $table) {
            if (! Schema::hasColumn('stoma_payment_card', 'stripe_payment_method_id')) {
                $table->string('stripe_payment_method_id', 255)->nullable()->after('card_brand');
            }
            if (! Schema::hasColumn('stoma_payment_card', 'stripe_customer_id')) {
                $table->string('stripe_customer_id', 255)->nullable()->after('stripe_payment_method_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stoma_payment_card', function (Blueprint $table) {
            if (Schema::hasColumn('stoma_payment_card', 'stripe_customer_id')) {
                $table->dropColumn('stripe_customer_id');
            }
            if (Schema::hasColumn('stoma_payment_card', 'stripe_payment_method_id')) {
                $table->dropColumn('stripe_payment_method_id');
            }
        });
    }
};
