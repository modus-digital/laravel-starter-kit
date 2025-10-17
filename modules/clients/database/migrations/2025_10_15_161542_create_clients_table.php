<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status')->default(ActivityStatus::ACTIVE);
            $table->string('website')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_user', function (Blueprint $table) {
            $table->foreignUuid('client_id')->index();
            $table->foreignUuid('user_id')->index();
            $table->timestamps();
        });
    }
};
