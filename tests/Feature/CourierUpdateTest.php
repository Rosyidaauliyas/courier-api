<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_put_replaces_courier_data_in_the_database(): void
    {
        $courier = Courier::factory()->level(1)->create();

        $payload = [
            'name' => 'Nama Baru',
            'phone' => '081311112222',
            'email' => 'baru@example.com',
            'address' => 'Alamat baru',
            'vehicle_type' => 'car',
            'vehicle_plate' => 'W 9999 ZZ',
            'level' => 5,
            'status' => 'suspended',
        ];

        $this->putJson("/api/couriers/{$courier->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.level', 5);

        $this->assertDatabaseHas('couriers', ['id' => $courier->id] + $payload);
    }

    public function test_patch_updates_only_given_fields(): void
    {
        $courier = Courier::factory()->level(1)->create(['name' => 'Tetap']);

        $this->patchJson("/api/couriers/{$courier->id}", ['level' => 4])->assertOk();

        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => 'Tetap', 'level' => 4]);
    }

    public function test_put_requires_required_fields(): void
    {
        $courier = Courier::factory()->create();

        $this->putJson("/api/couriers/{$courier->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'vehicle_type', 'level']);
    }

    public function test_patch_still_validates_given_fields(): void
    {
        $courier = Courier::factory()->create(['level' => 2]);

        $this->patchJson("/api/couriers/{$courier->id}", ['level' => 9])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('level');

        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'level' => 2]);
    }

    public function test_courier_can_keep_its_own_unique_values(): void
    {
        $courier = Courier::factory()->create(['phone' => '081234567890', 'email' => 'me@example.com']);

        $this->patchJson("/api/couriers/{$courier->id}", [
            'phone' => '081234567890',
            'email' => 'me@example.com',
            'name' => 'Ganti Nama',
        ])->assertOk();
    }

    public function test_cannot_use_another_couriers_phone_or_email(): void
    {
        Courier::factory()->create(['phone' => '081200000001', 'email' => 'a@example.com']);
        $courier = Courier::factory()->create();

        $this->patchJson("/api/couriers/{$courier->id}", [
            'phone' => '081200000001',
            'email' => 'a@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['phone', 'email']);
    }

    public function test_returns_404_when_not_found(): void
    {
        $this->patchJson('/api/couriers/999', ['level' => 2])->assertNotFound();
    }
}
