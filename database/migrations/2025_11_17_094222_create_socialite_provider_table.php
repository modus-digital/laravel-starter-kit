<?php

declare(strict_types=1);

use App\Enums\AuthenticationProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        // Create the base table for the socialite providers
        Schema::create('socialite_providers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add the provider column to the users table
        Schema::table('users', function (Blueprint $table): void {
            $table->string('provider')
                ->nullable()
                ->after('password')
                ->default(AuthenticationProvider::EMAIL);
        });

        // Insert the default providers specified in the config (config/modules.php)
        foreach (config('modules.socialite.providers') as $provider => $enabled) {
            DB::table('socialite_providers')->insert([
                'id' => Str::uuid7(),
                'name' => AuthenticationProvider::from($provider),
                'is_enabled' => $enabled,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socialite_providers');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('provider');
        });
    }
};
