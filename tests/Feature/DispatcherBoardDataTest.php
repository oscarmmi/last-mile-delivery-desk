<?php

namespace Tests\Feature;

use App\Enums\StopStatus;
use App\Models\Driver;
use App\Models\Stop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatcherBoardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_board_groups_driver_stops_and_exposes_counts_and_unassigned_work(): void
    {
        $dispatcher = User::factory()->create();
        $driver = Driver::factory()->create(['name' => 'Camila Rojas']);
        Stop::factory()->create([
            'driver_id' => $driver->id,
            'status' => StopStatus::Queued,
            'address' => 'Calle 45 #18-20, Cabecera',
        ]);
        Stop::factory()->create([
            'driver_id' => null,
            'status' => StopStatus::OutForDelivery,
            'address' => 'Carrera 33 #48-10, Sotomayor',
        ]);

        $this->actingAs($dispatcher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('drivers.0.name', 'Camila Rojas')
                ->where('drivers.0.counts.queued', 1)
                ->where('drivers.0.stops.0.status', 'queued')
                ->where('unassignedStops.0.address', 'Carrera 33 #48-10, Sotomayor')
                ->where('statusCounts.queued', 1)
                ->where('statusCounts.out_for_delivery', 1)
                ->where('totalDrivers', 1)
                ->where('activeDriverCount', 1));
    }
}
