<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccessRequest extends Model
{
    protected $fillable = ['name', 'reason', 'ip_address', 'status', 'approved_at', 'used_at'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime', 'used_at' => 'datetime'];
    }

    // Status efektif: kedaluwarsa dihitung dari waktu
    public function effectiveStatus(): string
    {
        if ($this->status === 'pending' && $this->created_at->lt(now()->subMinutes(30))) {
            return 'expired';
        }

        if ($this->status === 'approved' && $this->approved_at->lt(now()->subMinutes(15))) {
            return 'expired';
        }

        return $this->status;
    }
}