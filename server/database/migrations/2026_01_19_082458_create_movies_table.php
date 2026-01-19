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
    Schema::create('movies', function (Blueprint $table) {
        $table->id();

        $table->string('title', 191)->unique();

        $table->year('produced');
        $table->smallInteger('length');

        $table->date('premiere');

        $table->string('watchlink', 191)->unique();
        $table->string('imdblink', 191)->unique();
        $table->string('insetlink', 191)->unique();

        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
