<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentCardIdToStomaPaidModuleTable extends Migration
{
    public function up(): void
    {
        Schema::table('stoma_paid_module', function (Blueprint $table) {
            if (! Schema::hasColumn('stoma_paid_module', 'payment_card_id')) {
                $table->unsignedInteger('payment_card_id')->nullable()->after('billing_cycle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stoma_paid_module', function (Blueprint $table) {
            if (Schema::hasColumn('stoma_paid_module', 'payment_card_id')) {
                $table->dropColumn('payment_card_id');
            }
        });
    }
}
