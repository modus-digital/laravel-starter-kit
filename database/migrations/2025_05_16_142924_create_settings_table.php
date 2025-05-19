<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the settings table
        Schema::create(config('settings.database_table_name'), function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Insert the default settings
        DB::table(config('settings.database_table_name'))->insert([
            ['key' => 'general.app_name', 'value' => 'Laravel', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['key' => 'general.logo', 'value' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['key' => 'features.auth.register', 'value' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['key' => 'features.auth.login', 'value' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['key' => 'features.auth.password_reset', 'value' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['key' => 'features.auth.email_verification', 'value' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists(config('settings.database_table_name'));
    }
};
