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
        Schema::create('task_views', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('taskable');
            $table->string('type');
            $table->string('name');
            $table->foreignUuid('created_by_id')->nullable()->constrained('users');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['taskable_type', 'taskable_id', 'type'], 'task_views_taskable_type_taskable_id_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_views');
    }
};
