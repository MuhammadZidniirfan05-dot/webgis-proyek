<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('status')->default('perencanaan');
        $table->decimal('budget', 18, 2)->default(0);
        $table->date('start_date')->nullable();
        $table->date('deadline')->nullable();
        $table->unsignedTinyInteger('progress_percent')->default(0);
        $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
        $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });

    DB::statement('ALTER TABLE projects ADD COLUMN geom geometry(Geometry, 4326)');
    DB::statement('CREATE INDEX projects_geom_gist ON projects USING GIST (geom)');
}

public function down(): void
{
    Schema::dropIfExists('projects');
}
};
