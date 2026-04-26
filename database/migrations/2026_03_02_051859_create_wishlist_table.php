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
      Schema::create('wishlist', function (Blueprint $table) {
    $table->id('id_wishlist');

    $table->unsignedBigInteger('id_user');
    $table->unsignedBigInteger('id_destinasi');

    // Relasi ke users (kolomnya = id)
    $table->foreign('id_user')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');

    // Relasi ke destinasi (kolomnya = id_destinasi)
    $table->foreign('id_destinasi')
          ->references('id_destinasi')
          ->on('destinasi')
          ->onDelete('cascade');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};
