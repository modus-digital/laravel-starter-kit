<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ModusDigital\SocialAuthentication\Enums\AuthenticationProvider;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider'); // google, github, facebook, etc.
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // Will be encrypted
            $table->string('redirect_uri')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['provider']);
        });

        // Add provider column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('email');
        });

        DB::table('users')->whereNull('provider')->update(['provider' => AuthenticationProvider::EMAIL->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('provider');
        });

        Schema::dropIfExists('oauth_providers');

    }
};
