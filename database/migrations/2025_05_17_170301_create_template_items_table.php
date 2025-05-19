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
        Schema::create('template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');

            $table->string('type'); // 'input', 'textarea', 'formField', etc.
            $table->integer('left')->nullable();
            $table->integer('top')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('class_name')->nullable();

            $table->text('value')->nullable(); // could be plain text or JSON
            $table->json('data')->nullable();  // config for formField, studentForm...
            $table->integer('rows')->nullable();
            $table->integer('columns')->nullable();
            $table->json('column_ratios')->nullable();
            $table->json('nested_config')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_items');
    }
};
