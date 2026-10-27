<?php

namespace App\Http\Controllers;

use App\Enums\StopStatus;
use App\Models\Stop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StopController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'window_starts_at' => ['required', 'date'],
            'window_ends_at' => ['required', 'date', 'after_or_equal:window_starts_at'],
        ]);

        Stop::create([
            ...$validated,
            'status' => StopStatus::Queued,
        ]);

        return back()->with('success', 'Stop added to the dispatch queue.');
    }

    public function assign(Request $request, Stop $stop): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => [
                'nullable',
                'integer',
                Rule::exists('drivers', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $stop->update($validated);

        return back()->with(
            'success',
            $stop->driver_id === null ? 'Stop returned to the unassigned queue.' : 'Stop assigned to a driver.',
        );
    }

    public function updateStatus(Request $request, Stop $stop): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StopStatus::class)],
        ]);

        $stop->transitionTo(StopStatus::from($validated['status']));

        return back()->with('success', 'Stop status updated.');
    }
}
