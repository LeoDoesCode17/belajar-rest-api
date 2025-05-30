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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('street', 200)->nullable(true);
            $table->string('city', 100)->nullable(true);
            $table->string('province', 100)->nullable(true);
            $table->string('country', 100)->nullable(false);
            $table->string('postal_code', 10)->nullable(true);
            $table->foreignId('contact_id')->nullable(false)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
