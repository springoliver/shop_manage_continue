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
        Schema::table('stoma_paid_module', function (Blueprint $table) {
            if (! Schema::hasColumn('stoma_paid_module', 'auto_renew')) {
                $table->boolean('auto_renew')->default(0)->after('isTrial');
            }
            if (! Schema::hasColumn('stoma_paid_module', 'billing_cycle')) {
                $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('auto_renew');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stoma_paid_module', function (Blueprint $table) {
            if (Schema::hasColumn('stoma_paid_module', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
            if (Schema::hasColumn('stoma_paid_module', 'auto_renew')) {
                $table->dropColumn('auto_renew');
            }
        });
    }
};
