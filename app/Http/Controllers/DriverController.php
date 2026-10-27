<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DriverController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Drivers/Index', [
            'drivers' => Driver::query()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Driver::create($this->validatedData($request));

        return back()->with('success', 'Driver added to your team.');
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $driver->update($this->validatedData($request));

        return back()->with('success', 'Driver details updated.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $driver->delete();

        return back()->with('success', 'Driver removed from your team.');
    }

    /** @return array{name: string, phone: ?string, vehicle: string, is_active: bool} */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'vehicle' => ['required', 'string', 'max:60'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
