<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const TABLE_NAME = 'application_settings';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table): void {
            $table->string('name')->unique();
            $table->string('value');
            $table->timestamps();
        });

        DB::table(self::TABLE_NAME)->insert([
            ['name' => 'app.name', 'value' => 'Modus', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'app.features.auth.register', 'value' => 'enabled', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'app.features.auth.login', 'value' => 'enabled', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'app.features.auth.two_factor_authentication', 'value' => 'enabled', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'app.features.auth.password_reset', 'value' => 'enabled', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'app.features.auth.email_verification', 'value' => 'enabled', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'app.features.translation_manager', 'value' => 'disabled', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
};
