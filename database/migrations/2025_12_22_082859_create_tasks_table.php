<?php

declare(strict_types=1);

use App\Enums\Modules\Tasks\TaskPriority;
use App\Enums\Modules\Tasks\TaskType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const array DEFAULT_STATUSES = [
        ['id' => '019b454e-e215-72ef-b6c5-b40d67c6e8a2', 'name' => 'Todo', 'color' => '#3498db'],
        ['id' => '019b454f-91af-7054-a201-fbd3bc129897', 'name' => 'In Progress', 'color' => '#f1c40f'],
        ['id' => '019b454f-d915-73e4-a4e0-e74b9cf612b6', 'name' => 'Done', 'color' => '#2ecc71'],
    ];

    public function shouldRun(): bool
    {
        return config('modules.tasks.enabled') === true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('color');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('task_statuses')->insert(self::DEFAULT_STATUSES);

        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('taskable');
            $table->string('title');
            $table->string('type')->default(TaskType::TASK);
            $table->string('priority')->default(TaskPriority::NORMAL);
            $table->text('description')->nullable();
            $table->foreignUuid('status_id');
            $table->foreignUuid('created_by_id')->nullable();
            $table->foreignUuid('assigned_to_id')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_statuses');
    }
};
