<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierShowDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_all_courier_data(): void
    {
        $courier = Courier::factory()->create();

        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.name', $courier->name)
            ->assertJsonPath('data.phone', $courier->phone)
            ->assertJsonPath('data.email', $courier->email)
            ->assertJsonPath('data.address', $courier->address)
            ->assertJsonPath('data.vehicle_type', $courier->vehicle_type->value)
            ->assertJsonPath('data.vehicle_plate', $courier->vehicle_plate)
            ->assertJsonPath('data.level', $courier->level)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonStructure(['data' => ['created_at', 'updated_at']]);
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/couriers/999')->assertNotFound();
    }

    public function test_destroy_removes_courier_from_database(): void
    {
        $courier = Courier::factory()->create();
        $other = Courier::factory()->create();

        $this->deleteJson("/api/couriers/{$courier->id}")->assertNoContent();

        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
        $this->assertDatabaseHas('couriers', ['id' => $other->id]);
    }

    public function test_destroy_returns_404_when_not_found(): void
    {
        $this->deleteJson('/api/couriers/999')->assertNotFound();
    }
}
