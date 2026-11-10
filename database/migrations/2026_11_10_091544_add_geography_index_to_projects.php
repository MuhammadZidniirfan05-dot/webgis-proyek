<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS projects_geom_geog_gist ON projects USING GIST ((geom::geography))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS projects_geom_geog_gist');
    }
};