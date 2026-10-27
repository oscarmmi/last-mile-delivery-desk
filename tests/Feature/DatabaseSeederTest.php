<?php

use App\Enums\StopStatus;
use App\Models\Driver;
use App\Models\Stop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the demo seeder creates four drivers and thirty Bucaramanga stops idempotently', function () {
    $this->seed();

    expect(User::where('email', 'dispatcher@example.com')->count())->toBe(1)
        ->and(Driver::count())->toBe(4)
        ->and(Stop::count())->toBe(30)
        ->and(Stop::whereNull('driver_id')->count())->toBe(6)
        ->and(Stop::where('status', StopStatus::Queued->value)->count())->toBe(12)
        ->and(Stop::where('status', StopStatus::OutForDelivery->value)->count())->toBe(8)
        ->and(Stop::where('status', StopStatus::Delivered->value)->count())->toBe(8)
        ->and(Stop::where('status', StopStatus::Failed->value)->count())->toBe(2);

    $this->seed();

    expect(User::count())->toBe(1)
        ->and(Driver::count())->toBe(4)
        ->and(Stop::count())->toBe(30);
});
