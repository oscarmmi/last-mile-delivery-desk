<?php

use App\Enums\StopStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address');
            $table->dateTime('window_starts_at');
            $table->dateTime('window_ends_at');
            $table->enum('status', array_column(StopStatus::cases(), 'value'))
                ->default(StopStatus::Queued->value)
                ->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
