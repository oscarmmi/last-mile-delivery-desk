<?php

namespace App\Http\Controllers;

use App\Enums\StopStatus;
use App\Models\Driver;
use App\Models\Stop;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class DispatcherController extends Controller
{
    public function index(): Response
    {
        $drivers = Driver::query()
            ->with(['stops' => fn ($query) => $query->orderBy('window_starts_at')])
            ->withCount([
                'stops as queued_count' => fn (Builder $query) => $query->where('status', StopStatus::Queued->value),
                'stops as out_for_delivery_count' => fn (Builder $query) => $query->where('status', StopStatus::OutForDelivery->value),
                'stops as delivered_count' => fn (Builder $query) => $query->where('status', StopStatus::Delivered->value),
                'stops as failed_count' => fn (Builder $query) => $query->where('status', StopStatus::Failed->value),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Driver $driver) => [
                'id' => $driver->id,
                'name' => $driver->name,
                'vehicle' => $driver->vehicle,
                'is_active' => $driver->is_active,
                'counts' => [
                    'queued' => $driver->queued_count,
                    'out_for_delivery' => $driver->out_for_delivery_count,
                    'delivered' => $driver->delivered_count,
                    'failed' => $driver->failed_count,
                ],
                'stops' => $driver->stops->map(fn (Stop $stop) => $this->serializeStop($stop))->values(),
            ])
            ->values();

        $statusCounts = Stop::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Dashboard', [
            'drivers' => $drivers,
            'unassignedStops' => Stop::query()
                ->whereNull('driver_id')
                ->orderBy('window_starts_at')
                ->get()
                ->map(fn (Stop $stop) => $this->serializeStop($stop))
                ->values(),
            'statusCounts' => collect(StopStatus::cases())
                ->mapWithKeys(fn (StopStatus $status) => [$status->value => (int) ($statusCounts[$status->value] ?? 0)]),
            'totalDrivers' => Driver::count(),
            'activeDriverCount' => Driver::where('is_active', true)->count(),
        ]);
    }

    /** @return array{id: int, driver_id: ?int, address: string, window: string, status: string} */
    private function serializeStop(Stop $stop): array
    {
        return [
            'id' => $stop->id,
            'driver_id' => $stop->driver_id,
            'address' => $stop->address,
            'window' => $stop->window_starts_at->format('g:i A').' – '.$stop->window_ends_at->format('g:i A'),
            'status' => $stop->status->value,
        ];
    }
}
