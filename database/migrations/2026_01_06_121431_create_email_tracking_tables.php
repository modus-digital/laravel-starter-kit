<?php

declare(strict_types=1);

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
        return config('modules.mailgun_analytics.enabled') === true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('mailable_class')->nullable();
            $table->string('subject');
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->string('to_address')->index();
            $table->string('to_name')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('tags')->nullable();
            $table->string('mailgun_message_id')->nullable()->unique();
            $table->uuid('correlation_id')->unique();
            $table->string('status')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sent_at']);
            $table->index(['to_address', 'sent_at']);
        });

        Schema::create('email_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('email_message_id')->index();
            $table->string('event_type')->index();
            $table->string('mailgun_event_id')->unique();
            $table->string('severity')->nullable();
            $table->text('reason')->nullable();
            $table->string('recipient');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->json('raw_payload');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('email_message_id')
                ->references('id')
                ->on('email_messages')
                ->onDelete('cascade');

            $table->index(['email_message_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_events');
        Schema::dropIfExists('email_messages');
    }
};
