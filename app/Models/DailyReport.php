<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use Auditable;

    protected $fillable = ['project_id', 'user_id', 'report_date', 'progress_percent', 'notes'];

    protected function casts(): array
    {
        return ['report_date' => 'date', 'progress_percent' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function auditLabel(): string
    {
        return 'Laporan ' . $this->report_date?->format('d M Y') . ' (proyek #' . $this->project_id . ')';
    }
}