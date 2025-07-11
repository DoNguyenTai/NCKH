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
        Schema::create('field_forms', function (Blueprint $table) {
            $table->id();
            $table->integer('form_id');
            $table->string('key')->nullable();
            $table->string('data_type');
            $table->string('label');
            $table->json('options')->nullable();
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_forms');
    }
};
