<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Outerweb\Settings\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Create the settings table
        Schema::create(config('settings.database_table_name'), function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique()->index();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Setting::set('general.app_name', 'Laravel');
        Setting::set('general.logo', null);
        Setting::set('features.auth.register', true);
        Setting::set('features.auth.login', true);
        Setting::set('features.auth.password_reset', true);
        Setting::set('features.auth.email_verification', true);
    }

    public function down(): void
    {
        Schema::dropIfExists(config('settings.database_table_name'));
    }
};
