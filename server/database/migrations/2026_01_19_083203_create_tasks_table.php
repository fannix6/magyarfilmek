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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('roleid')->constrained('roles')->onDelete('cascade');
        $table->foreignId('personid')->constrained('people')->onDelete('cascade');
        $table->foreignId('movieid')->constrained('movies')->onDelete('restrict');
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
