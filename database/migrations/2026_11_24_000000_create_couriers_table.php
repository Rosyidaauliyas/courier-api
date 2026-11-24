<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable()->unique();
            $table->text('address')->nullable();
            $table->string('vehicle_type', 20);
            $table->string('vehicle_plate', 15)->nullable();
            $table->unsignedTinyInteger('level')->default(1)->index(); // 1-5
            $table->string('status', 20)->default('active')->index();
            $table->timestamps(); // created_at = tanggal didaftarkan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
