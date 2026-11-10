<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use Auditable;

    public const STATUSES = ['perencanaan', 'berjalan', 'selesai', 'ditunda'];

    // geom sengaja tidak masuk fillable: diisi lewat setGeometryFromGeoJson()
    protected $fillable = [
        'code', 'name', 'description', 'status', 'budget',
        'start_date', 'deadline', 'progress_percent',
        'region_id', 'manager_id',
    ];

    // geom berisi data biner (hex), tidak berguna untuk tampilan
    protected $hidden = ['geom'];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'deadline' => 'date',
            'progress_percent' => 'integer',
        ];
    }

    // ---------- Relasi ----------

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function officers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    // ---------- Status "terlambat" (dihitung, tidak disimpan) ----------

    protected function isLate(): Attribute
    {
        return Attribute::get(fn () => $this->deadline !== null
            && $this->deadline->lt(today())
            && $this->status !== 'selesai');
    }

    protected function displayStatus(): Attribute
    {
        return Attribute::get(fn () => $this->is_late ? 'terlambat' : $this->status);
    }

    public function scopeLate(Builder $query): Builder
    {
        return $query->whereNotNull('deadline')
            ->whereDate('deadline', '<', today())
            ->where('status', '!=', 'selesai');
    }

    // Filter status. "terlambat" dihitung dari deadline, bukan kolom status.
    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        if ($status === 'terlambat') {
            return $query->late();
        }

        // Status biasa: jangan ikutkan yang sebenarnya sudah terlambat
        return $query->where('status', $status)
            ->where(function ($q) use ($status) {
                if ($status === 'selesai') {
                    return; // proyek selesai tidak pernah terlambat
                }
                $q->whereNull('deadline')->orWhereDate('deadline', '>=', today());
            });
    }

    // ---------- Geometri (PostGIS) ----------

    // Menambahkan kolom `geojson` (string) ke hasil query
    public function scopeWithGeoJson(Builder $query): Builder
    {
        return $query->select('projects.*')
            ->selectRaw('ST_AsGeoJSON(projects.geom) as geojson');
    }

    // Proyek dalam radius (meter) dari titik lat/lng, memakai ST_DWithin
    public function scopeWithinRadius(Builder $query, float $lat, float $lng, float $meters): Builder
    {
        return $query->whereRaw(
            'ST_DWithin(projects.geom::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
            [$lng, $lat, $meters]
        );
    }

    // Simpan geometri dari GeoJSON (boleh Feature atau geometry langsung).
    // Panggil SETELAH proyek tersimpan (punya id).
    public function setGeometryFromGeoJson(string|array $geojson): void
    {
        $data = is_array($geojson) ? $geojson : json_decode($geojson, true);
        $geometry = $data['geometry'] ?? $data;

        DB::update(
            'UPDATE projects SET geom = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?',
            [json_encode($geometry), $this->id]
        );

        $this->auditGeometryChanged();
    }

    // ---------- Progres ----------

    // Sinkronkan progres proyek dengan laporan terbaru
    public function syncProgress(): void
    {
        $latest = $this->reports()->orderByDesc('report_date')->orderByDesc('id')->first();

        if ($latest) {
            $this->update(['progress_percent' => $latest->progress_percent]);
        }
    }

    // ---------- Audit ----------

    public function auditLabel(): string
    {
        return $this->code . ' - ' . $this->name;
    }
        // Kode berikutnya: angka terbesar dari kode berpola PRJ-<angka>, ditambah satu.
    // Kode berpola lain (mis. TES-XXXX) diabaikan.
    public static function nextCode(): string
    {
        $max = (int) static::query()
            ->selectRaw("MAX(CAST(substring(code from '^PRJ-([0-9]+)$') AS INTEGER)) as m")
            ->value('m');

        return sprintf('PRJ-%04d', $max + 1);
    }
}