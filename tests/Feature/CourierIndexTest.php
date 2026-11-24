<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CourierIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_paginated_structure(): void
    {
        Courier::factory()->count(20)->create();

        $this->getJson('/api/couriers')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'phone', 'email', 'address', 'vehicle_type', 'vehicle_plate', 'level', 'status', 'created_at', 'updated_at']],
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 20);
    }

    public function test_per_page_and_page_are_respected(): void
    {
        Courier::factory()->count(5)->create();

        $this->getJson('/api/couriers?per_page=2&page=3')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 3)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_default_sort_is_by_name_ascending(): void
    {
        Courier::factory()->create(['name' => 'Charlie']);
        Courier::factory()->create(['name' => 'Alpha']);
        Courier::factory()->create(['name' => 'Bravo']);

        $response = $this->getJson('/api/couriers');

        $this->assertSame(['Alpha', 'Bravo', 'Charlie'], $response->json('data.*.name'));
    }

    public function test_can_sort_by_registration_date(): void
    {
        Courier::factory()->create(['name' => 'Alpha', 'created_at' => '2026-03-01 10:00:00']);
        Courier::factory()->create(['name' => 'Bravo', 'created_at' => '2026-01-01 10:00:00']);
        Courier::factory()->create(['name' => 'Charlie', 'created_at' => '2026-02-01 10:00:00']);

        $asc = $this->getJson('/api/couriers?sort=created_at');
        $desc = $this->getJson('/api/couriers?sort=created_at&direction=desc');

        $this->assertSame(['Bravo', 'Charlie', 'Alpha'], $asc->json('data.*.name'));
        $this->assertSame(['Alpha', 'Charlie', 'Bravo'], $desc->json('data.*.name'));
    }

    public function test_search_matches_partial_words_in_any_order(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Siti Aminah']);
        Courier::factory()->create(['name' => 'Agung Santoso']);

        $this->getJson('/api/couriers?search=budi+agung')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');

        $this->getJson('/api/couriers?search=agung+budi')->assertJsonCount(1, 'data');
        $this->getJson('/api/couriers?search=agung')->assertJsonCount(2, 'data');
    }

    public function test_search_ignores_like_wildcards(): void
    {
        Courier::factory()->create(['name' => 'Budi']);

        // "%" dibuang sehingga tidak berfungsi sebagai wildcard -> tanpa filter.
        $this->getJson('/api/couriers?search=%25')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_multiple_levels(): void
    {
        foreach (range(1, 5) as $level) {
            Courier::factory()->level($level)->create();
        }

        $response = $this->getJson('/api/couriers?level=2,3');

        $levels = $response->json('data.*.level');
        sort($levels);

        $response->assertOk()->assertJsonCount(2, 'data');
        $this->assertSame([2, 3], $levels);
    }

    public function test_can_filter_by_single_level(): void
    {
        Courier::factory()->level(4)->count(2)->create();
        Courier::factory()->level(1)->create();

        $this->getJson('/api/couriers?level=4')->assertJsonCount(2, 'data');
    }

    public function test_filters_can_be_combined(): void
    {
        Courier::factory()->level(2)->create(['name' => 'Budi Santoso']);
        Courier::factory()->level(5)->create(['name' => 'Budi Hartono']);
        Courier::factory()->level(2)->create(['name' => 'Siti']);

        $this->getJson('/api/couriers?search=budi&level=2,3')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budi Santoso');
    }

    #[DataProvider('invalidQueryProvider')]
    public function test_rejects_invalid_query_params(string $query, string $field): void
    {
        $this->getJson('/api/couriers?'.$query)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public static function invalidQueryProvider(): array
    {
        return [
            'level out of range' => ['level=6', 'level'],
            'level not numeric' => ['level=abc', 'level'],
            'level bad format' => ['level=2,,3', 'level'],
            'sort not whitelisted' => ['sort=password', 'sort'],
            'direction invalid' => ['direction=sideways', 'direction'],
            'per_page too large' => ['per_page=1000', 'per_page'],
            'per_page zero' => ['per_page=0', 'per_page'],
        ];
    }
}
