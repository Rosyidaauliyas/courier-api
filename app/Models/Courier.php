<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    public const MIN_LEVEL = 1;

    public const MAX_LEVEL = 5;

    /** Kolom yang boleh dipakai untuk sorting dari luar (whitelist). */
    public const SORTABLE_FIELDS = ['name', 'created_at'];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'vehicle_type',
        'vehicle_plate',
        'level',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'vehicle_type' => VehicleType::class,
            'status' => CourierStatus::class,
        ];
    }

    /**
     * Cari berdasarkan nama. Input dipecah per kata dan SEMUA kata harus ada
     * di nama (urutan bebas), sehingga "budi agung" cocok dengan "Budiono Hadi Agung".
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        // Karakter wildcard LIKE dibuang agar input user tidak menjadi pattern.
        $clean = preg_replace('/[%_\\\\]/', '', (string) $term);
        $words = preg_split('/\s+/', trim($clean), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            $query->where('name', 'like', "%{$word}%");
        }

        return $query;
    }

    /**
     * @param  array<int, int>  $levels  kosong = tanpa filter
     */
    public function scopeWithLevels(Builder $query, array $levels): Builder
    {
        return $levels === [] ? $query : $query->whereIn('level', $levels);
    }

    public function scopeSortBy(Builder $query, string $field = 'name', string $direction = 'asc'): Builder
    {
        $field = in_array($field, self::SORTABLE_FIELDS, true) ? $field : 'name';
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        // `id` sebagai tie-breaker supaya pagination deterministik.
        return $query->orderBy($field, $direction)->orderBy('id', $direction);
    }
}
