<?php

namespace App\Models;

use App\Enums\StopStatus;
use Database\Factories\StopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Stop extends Model
{
    /** @use HasFactory<StopFactory> */
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'address',
        'window_starts_at',
        'window_ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'window_starts_at' => 'datetime',
            'window_ends_at' => 'datetime',
            'status' => StopStatus::class,
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function transitionTo(StopStatus $nextStatus): void
    {
        $allowedTransitions = match ($this->status) {
            StopStatus::Queued => [StopStatus::OutForDelivery],
            StopStatus::OutForDelivery => [StopStatus::Delivered, StopStatus::Failed],
            StopStatus::Delivered, StopStatus::Failed => [],
        };

        if (! in_array($nextStatus, $allowedTransitions, true)) {
            throw ValidationException::withMessages([
                'status' => "A stop cannot move from {$this->status->value} to {$nextStatus->value}.",
            ]);
        }

        $this->update(['status' => $nextStatus]);
    }
}
