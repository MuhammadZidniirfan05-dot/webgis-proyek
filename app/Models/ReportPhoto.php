<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportPhoto extends Model
{
    use Auditable;

    protected $fillable = ['daily_report_id', 'path', 'caption'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    public function auditLabel(): string
    {
        return 'Foto ' . basename($this->path);
    }
}