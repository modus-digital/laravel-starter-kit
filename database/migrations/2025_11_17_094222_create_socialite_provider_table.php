<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Determine if the migrations should run.
     */
    public function shouldRun(): bool
    {
        return config(key: 'modules.socialite.enabled', default: false) === true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('socialite_provider', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socialite_provider');
    }
};
