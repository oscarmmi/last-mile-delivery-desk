<?php

namespace Database\Seeders;

use App\Enums\StopStatus;
use App\Models\Driver;
use App\Models\Stop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'dispatcher@example.com'],
            [
                'name' => 'Dispatch Manager',
                'password' => 'password',
            ],
        );

        $drivers = collect([
            ['name' => 'Camila Rojas', 'phone' => '+57 300 410 2381', 'vehicle' => 'Motorcycle'],
            ['name' => 'Andrés Pardo', 'phone' => '+57 301 562 9044', 'vehicle' => 'Motorcycle'],
            ['name' => 'Lucía Gómez', 'phone' => '+57 302 773 1160', 'vehicle' => 'Bicycle'],
            ['name' => 'Mateo Silva', 'phone' => '+57 310 884 5273', 'vehicle' => 'Van'],
        ])->mapWithKeys(fn (array $attributes) => [
            $attributes['name'] => Driver::updateOrCreate(['name' => $attributes['name']], $attributes)->id,
        ]);

        $addresses = [
            'Calle 48 #29-14, Cabecera',
            'Carrera 35 #52-08, Cabecera',
            'Calle 45 #18-26, Sotomayor',
            'Carrera 27 #41-17, Mejoras Públicas',
            'Calle 34 #26-51, San Alonso',
            'Carrera 33 #48-10, Sotomayor',
            'Calle 56 #31-22, Cabecera',
            'Carrera 23 #37-06, Centro',
            'Calle 14 #28-45, San Francisco',
            'Carrera 21 #15-38, Alarcón',
            'Calle 52 #35-19, Cabecera',
            'Carrera 29 #45-12, Sotomayor',
            'Calle 33 #19-08, San Alonso',
            'Carrera 36 #42-31, Mejoras Públicas',
            'Calle 61 #18-07, La Aurora',
            'Carrera 17 #33-24, San Francisco',
            'Calle 50 #27-16, Cabecera',
            'Carrera 24 #39-52, Centro',
            'Calle 91 #25-40, Lagos del Cacique',
            'Carrera 21 #54-15, Provenza',
            'Calle 63 #16-22, La Aurora',
            'Carrera 30 #31-09, San Alonso',
            'Calle 105 #23-18, Lagos del Cacique',
            'Carrera 19 #58-37, Provenza',
            'Calle 56 #8-13, Mutis',
            'Carrera 4 #61-25, Real de Minas',
            'Calle 44 #32-06, Cabecera',
            'Carrera 28 #46-19, Sotomayor',
            'Calle 30 #20-42, San Francisco',
            'Carrera 22 #36-11, Centro',
        ];

        $statuses = [
            ...array_fill(0, 12, StopStatus::Queued),
            ...array_fill(0, 8, StopStatus::OutForDelivery),
            ...array_fill(0, 8, StopStatus::Delivered),
            ...array_fill(0, 2, StopStatus::Failed),
        ];
        $driverNames = [null, 'Camila Rojas', 'Andrés Pardo', 'Lucía Gómez', 'Mateo Silva'];
        $firstWindow = today()->setTime(8, 0);

        foreach ($addresses as $index => $address) {
            $windowStartsAt = $firstWindow->copy()->addMinutes($index * 17);
            $driverName = $driverNames[intdiv($index, 6)];

            Stop::updateOrCreate(
                ['address' => $address],
                [
                    'driver_id' => $driverName === null ? null : $drivers[$driverName],
                    'window_starts_at' => $windowStartsAt,
                    'window_ends_at' => $windowStartsAt->copy()->addHour(),
                    'status' => $statuses[$index],
                ],
            );
        }
    }
}
