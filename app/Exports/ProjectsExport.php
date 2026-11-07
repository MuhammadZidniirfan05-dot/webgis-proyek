<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private ?string $status = null) {}

    public function query(): Builder
    {
        return Project::query()
            ->with(['region', 'manager'])
            ->withStatus($this->status)
            ->orderBy('code');
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Status', 'Wilayah', 'Manajer', 'Anggaran', 'Mulai', 'Deadline', 'Progres (%)'];
    }

    public function map($p): array
    {
        return [
            $p->code,
            $p->name,
            $p->display_status,
            $p->region?->name,
            $p->manager?->name,
            (float) $p->budget,
            $p->start_date?->format('Y-m-d'),
            $p->deadline?->format('Y-m-d'),
            $p->progress_percent,
        ];
    }
}