<?php

namespace Tests\Feature;

use App\Enums\StopStatus;
use App\Models\Stop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StopCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_can_create_a_queued_stop_with_a_time_window(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('stops.store'), [
                'address' => 'Calle 45 #18-20, Cabecera',
                'window_starts_at' => '2026-11-01 09:00:00',
                'window_ends_at' => '2026-11-01 11:00:00',
                'status' => StopStatus::Delivered->value,
            ])
            ->assertSessionHasNoErrors();

        $stop = Stop::query()->firstOrFail();

        $this->assertSame('Calle 45 #18-20, Cabecera', $stop->address);
        $this->assertNull($stop->driver_id);
        $this->assertSame(StopStatus::Queued, $stop->status);
        $this->assertSame('2026-11-01 09:00:00', $stop->window_starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-01 11:00:00', $stop->window_ends_at->format('Y-m-d H:i:s'));
    }

    public function test_dispatcher_cannot_create_a_stop_with_an_invalid_time_window(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('stops.store'), [
                'address' => 'Calle 45 #18-20, Cabecera',
                'window_starts_at' => '2026-11-01 11:00:00',
                'window_ends_at' => '2026-11-01 09:00:00',
            ])
            ->assertSessionHasErrors('window_ends_at');

        $this->assertDatabaseCount('stops', 0);
    }

    public function test_stop_status_is_a_backed_enum(): void
    {
        $this->assertSame(
            ['queued', 'out_for_delivery', 'delivered', 'failed'],
            array_map(fn (StopStatus $status) => $status->value, StopStatus::cases()),
        );
    }
}
