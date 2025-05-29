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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('type_code_id')->constrained()->restrictOnDelete();
            $table->string('book_code');
            $table->string('status')->default('disponible');
            $table->foreignId('publishing_house_id')->constrained()->restrictOnDelete();
            $table->string('publishing_year');
            $table->string('edition');
            $table->string('inventory_number');
            $table->string('physic_location');
            $table->text('themes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
