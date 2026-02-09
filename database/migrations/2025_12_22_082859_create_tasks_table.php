<?php

declare(strict_types=1);

use App\Enums\Modules\Tasks\TaskPriority;
use App\Enums\Modules\Tasks\TaskType;
use App\Enums\Modules\Tasks\TaskViewType;
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
        Schema::create('task_views', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('taskable');
            $table->boolean('is_default')->default(false);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default(TaskViewType::LIST);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('task_statuses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('color');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('taskable');
            $table->string('title');
            $table->string('type')->default(TaskType::TASK);
            $table->string('priority')->default(TaskPriority::NORMAL);
            $table->text('description')->nullable();
            $table->integer('order')->nullable();
            $table->foreignUuid('status_id')->constrained('task_statuses')->onDelete('cascade');
            $table->foreignUuid('created_by_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignUuid('assigned_to_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['taskable_type', 'taskable_id', 'status_id', 'order']);
        });

        Schema::create('task_view_statuses', function (Blueprint $table): void {
            $table->foreignUuid('task_view_id')->constrained('task_views')->onDelete('cascade');
            $table->foreignUuid('task_status_id')->constrained('task_statuses')->onDelete('cascade');
            $table->integer('position')->default(0);

            $table->unique(['task_view_id', 'task_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_view_statuses');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_statuses');
        Schema::dropIfExists('task_views');
    }
};
