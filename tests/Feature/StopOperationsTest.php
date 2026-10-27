<?php

use App\Enums\StopStatus;
use App\Models\Driver;
use App\Models\Stop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a dispatcher can assign and unassign a stop', function () {
    $dispatcher = User::factory()->create();
    $driver = Driver::factory()->create();
    $stop = Stop::factory()->create(['driver_id' => null]);

    $this->actingAs($dispatcher)
        ->patch(route('stops.assign', $stop), ['driver_id' => $driver->id])
        ->assertSessionHasNoErrors();

    expect($stop->fresh()->driver_id)->toBe($driver->id);

    $this->patch(route('stops.assign', $stop), ['driver_id' => null])
        ->assertSessionHasNoErrors();

    expect($stop->fresh()->driver_id)->toBeNull();
});

test('a stop cannot be assigned to an inactive driver', function () {
    $dispatcher = User::factory()->create();
    $driver = Driver::factory()->create(['is_active' => false]);
    $stop = Stop::factory()->create(['driver_id' => null]);

    $this->actingAs($dispatcher)
        ->patchJson(route('stops.assign', $stop), ['driver_id' => $driver->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('driver_id');

    expect($stop->fresh()->driver_id)->toBeNull();
});

test('a stop advances through queued, out for delivery, and delivered', function () {
    $dispatcher = User::factory()->create();
    $stop = Stop::factory()->create(['status' => StopStatus::Queued]);

    $this->actingAs($dispatcher)
        ->patch(route('stops.status', $stop), ['status' => StopStatus::OutForDelivery->value])
        ->assertSessionHasNoErrors();

    expect($stop->fresh()->status)->toBe(StopStatus::OutForDelivery);

    $this->patch(route('stops.status', $stop), ['status' => StopStatus::Delivered->value])
        ->assertSessionHasNoErrors();

    expect($stop->fresh()->status)->toBe(StopStatus::Delivered);
});

test('a stop can be marked failed after it is out for delivery', function () {
    $dispatcher = User::factory()->create();
    $stop = Stop::factory()->create(['status' => StopStatus::OutForDelivery]);

    $this->actingAs($dispatcher)
        ->patch(route('stops.status', $stop), ['status' => StopStatus::Failed->value])
        ->assertSessionHasNoErrors();

    expect($stop->fresh()->status)->toBe(StopStatus::Failed);
});

test('a queued stop cannot jump directly to delivered', function () {
    $dispatcher = User::factory()->create();
    $stop = Stop::factory()->create(['status' => StopStatus::Queued]);

    $this->actingAs($dispatcher)
        ->patchJson(route('stops.status', $stop), ['status' => StopStatus::Delivered->value])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');

    expect($stop->fresh()->status)->toBe(StopStatus::Queued);
});

test('a terminal stop cannot transition again', function () {
    $dispatcher = User::factory()->create();
    $stop = Stop::factory()->create(['status' => StopStatus::Failed]);

    $this->actingAs($dispatcher)
        ->patchJson(route('stops.status', $stop), ['status' => StopStatus::Delivered->value])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('an unknown stop status is rejected', function () {
    $dispatcher = User::factory()->create();
    $stop = Stop::factory()->create();

    $this->actingAs($dispatcher)
        ->patchJson(route('stops.status', $stop), ['status' => 'returned'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});
