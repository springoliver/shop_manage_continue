<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStomaPaymentCardTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stoma_payment_card', function (Blueprint $table) {
            $table->increments('cardid');
            $table->unsignedBigInteger('storeid');
            $table->unsignedBigInteger('ownerid');
            $table->string('name_on_card', 255);
            $table->string('card_last4', 4);
            $table->string('card_brand', 50)->nullable();
            $table->unsignedTinyInteger('expiry_month');
            $table->unsignedSmallInteger('expiry_year');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamp('insertdate')->nullable();
            $table->string('insertip', 51)->nullable();
            $table->timestamp('editdate')->nullable();
            $table->string('editip', 51)->nullable();

            $table->index(['storeid', 'ownerid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoma_payment_card');
    }
}
