<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_management_requires_dispatcher_authentication(): void
    {
        $this->get(route('drivers.index'))->assertRedirect(route('login'));
    }

    public function test_dispatcher_can_manage_drivers(): void
    {
        $dispatcher = User::factory()->create();

        $this->actingAs($dispatcher)
            ->get(route('drivers.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Drivers/Index')
                ->has('drivers', 0));

        $this->post(route('drivers.store'), [
            'name' => 'Jordan Lee',
            'phone' => '+57 300 555 0101',
            'vehicle' => 'Motorcycle',
            'is_active' => true,
        ])->assertSessionHasNoErrors();

        $driver = Driver::query()->firstOrFail();
        $this->assertSame('Jordan Lee', $driver->name);
        $this->assertTrue($driver->is_active);

        $this->put(route('drivers.update', $driver), [
            'name' => 'Jordan Lee',
            'phone' => null,
            'vehicle' => 'Bicycle',
            'is_active' => false,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Bicycle', $driver->fresh()->vehicle);
        $this->assertFalse($driver->fresh()->is_active);

        $this->delete(route('drivers.destroy', $driver))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }

    public function test_driver_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('drivers.index'))
            ->post(route('drivers.store'), [
                'name' => '',
                'phone' => str_repeat('1', 31),
                'vehicle' => '',
                'is_active' => 'sometimes',
            ])
            ->assertSessionHasErrors(['name', 'phone', 'vehicle', 'is_active']);
    }
}
