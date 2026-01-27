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
        Schema::create('stoma_module_dependency', function (Blueprint $table) {
            $table->id('dependencyid');
            $table->unsignedBigInteger('moduleid');
            $table->unsignedBigInteger('dependent_moduleid');

            $table->unique(['moduleid', 'dependent_moduleid'], 'stoma_module_dependency_unique');
            $table->foreign('moduleid')->references('moduleid')->on('stoma_module')->onDelete('cascade');
            $table->foreign('dependent_moduleid')->references('moduleid')->on('stoma_module')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoma_module_dependency');
    }
};
