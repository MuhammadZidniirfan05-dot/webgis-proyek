<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    // Kolom yang tidak dicatat nilainya
    protected static array $auditIgnore = ['updated_at', 'created_at', 'geom'];

    public static function bootAuditable(): void
    {
        static::created(fn (Model $m) => self::writeAudit($m, 'created', [], $m->getAttributes()));

        static::updated(function (Model $m) {
            $changes = $m->getChanges();
            $old = array_intersect_key($m->getOriginal(), $changes);
            self::writeAudit($m, 'updated', $old, $changes);
        });

        static::deleted(fn (Model $m) => self::writeAudit($m, 'deleted', $m->getAttributes(), []));
    }

    // Dipanggil manual saat geometri diubah (UPDATE geom lewat SQL mentah tidak memicu event)
    public function auditGeometryChanged(): void
    {
        self::writeAudit($this, 'updated', [], ['geom' => 'diubah']);
    }

    private static function writeAudit(Model $m, string $action, array $old, array $new): void
    {
        $ignore = static::$auditIgnore;
        $old = array_diff_key($old, array_flip($ignore));
        $new = array_diff_key($new, array_flip($ignore));

        // "updated" tanpa perubahan berarti tidak perlu dicatat
        if ($action === 'updated' && empty($new)) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $m::class,
            'auditable_id' => $m->getKey(),
            'label' => method_exists($m, 'auditLabel') ? $m->auditLabel() : null,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }
}