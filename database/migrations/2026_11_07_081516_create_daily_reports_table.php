<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up(): void
{
    Schema::create('daily_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->date('report_date');
        $table->unsignedTinyInteger('progress_percent');
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->index(['project_id', 'report_date']);
    });
}

public function down(): void
{
    Schema::dropIfExists('daily_reports');
}
};
