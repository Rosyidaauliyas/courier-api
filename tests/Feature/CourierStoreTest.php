<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CourierStoreTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budiono Hadi Agung',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'address' => 'Jl. Raya Darmo No. 1, Surabaya',
            'vehicle_type' => 'motorcycle',
            'vehicle_plate' => 'L 1234 AB',
            'level' => 3,
        ], $overrides);
    }

    public function test_it_stores_a_courier_in_the_database(): void
    {
        $this->postJson('/api/couriers', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Budiono Hadi Agung')
            ->assertJsonPath('data.level', 3)
            ->assertJsonPath('data.status', 'active'); // default

        $this->assertDatabaseHas('couriers', [
            'name' => 'Budiono Hadi Agung',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'vehicle_type' => 'motorcycle',
            'level' => 3,
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('couriers', 1);
    }

    public function test_optional_fields_may_be_omitted(): void
    {
        $this->postJson('/api/couriers', [
            'name' => 'Siti',
            'phone' => '085612345678',
            'vehicle_type' => 'bicycle',
            'level' => 1,
        ])->assertCreated();

        $this->assertDatabaseHas('couriers', ['phone' => '085612345678', 'email' => null]);
    }

    #[DataProvider('invalidPayloadProvider')]
    public function test_it_rejects_invalid_input(string $field, array $overrides): void
    {
        $this->postJson('/api/couriers', $this->validPayload($overrides))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('couriers', 0);
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'name required' => ['name', ['name' => '']],
            'name too long' => ['name', ['name' => str_repeat('a', 101)]],
            'phone required' => ['phone', ['phone' => '']],
            'phone bad format' => ['phone', ['phone' => '12345']],
            'email invalid' => ['email', ['email' => 'not-an-email']],
            'vehicle_type invalid' => ['vehicle_type', ['vehicle_type' => 'rocket']],
            'level required' => ['level', ['level' => null]],
            'level below range' => ['level', ['level' => 0]],
            'level above range' => ['level', ['level' => 6]],
            'level not integer' => ['level', ['level' => 'tinggi']],
            'status invalid' => ['status', ['status' => 'unknown']],
        ];
    }

    public function test_phone_must_be_unique(): void
    {
        Courier::factory()->create(['phone' => '081234567890']);

        $this->postJson('/api/couriers', $this->validPayload(['email' => 'other@example.com']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');
    }

    public function test_email_must_be_unique(): void
    {
        Courier::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/couriers', $this->validPayload(['phone' => '089912345678']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }
}
