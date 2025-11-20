<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
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
        return config('modules.clients.enabled') === true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('status')->default(ActivityStatus::ACTIVE);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('client_users', function (Blueprint $table) {
            $table->uuid('client_id')->index();
            $table->uuid('user_id')->index();
            $table->primary(['client_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_users');
    }
};
