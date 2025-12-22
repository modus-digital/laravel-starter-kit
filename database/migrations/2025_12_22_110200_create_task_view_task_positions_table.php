<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function shouldRun(): bool
    {
        return config('modules.tasks.enabled') === true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_view_task_positions', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('task_view_id')->constrained('task_views')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('task_status_id')->constrained('task_statuses');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['task_view_id', 'task_id'], 'task_view_task_positions_view_task_unique');
            $table->index(['task_view_id', 'task_status_id', 'position'], 'task_view_task_positions_view_status_position_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_view_task_positions');
    }
};
