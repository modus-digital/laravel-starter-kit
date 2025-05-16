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
        Schema::create('user_settings', function (Blueprint $table): void {
            $table->string(column: 'key', length: 255);
            $table->foreignId(column: 'user_id')->constrained(table: 'users');
            $table->json(column: 'value');

            $table->timestamps();
            $table->primary(columns: ['key', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
