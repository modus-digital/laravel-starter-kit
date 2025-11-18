<?php

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
        return config('modules.clients.enabled') === true
            && config('modules.saas') === true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('interval');
            $table->integer('trial_days')->nullable();
            $table->json('features')->nullable();
            $table->string('status')->default(ActivityStatus::ACTIVE);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('client_billing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            $table->string('company');
            $table->string('tax_number');
            $table->string('vat_number');
            $table->string('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country', 2);
            $table->string('billing_email');
            $table->string('billing_phone');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            $table->uuid('plan_id')->index();
            $table->string('status')->default(ActivityStatus::ACTIVE);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('restrict');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_id')->unique();
            $table->uuid('client_id')->index();
            $table->uuid('subscription_id')->index()->nullable();
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->index();
            $table->string('provider');
            $table->string('provider_payment_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('client_billing');
        Schema::dropIfExists('plans');
    }
};
