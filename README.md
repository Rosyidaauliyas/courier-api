# Courier API (Laravel)

REST API master data **Kurir**: CRUD, pagination, sorting, pencarian nama, dan filter level.

## Stack
PHP 8.2+, Laravel 11/12, SQLite (default; bisa diganti MySQL/PostgreSQL via `.env`).

## Instalasi
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # jika memakai SQLite
php artisan migrate
php artisan serve
```

## Menjalankan test
```bash
php artisan test
```
Test memakai SQLite in-memory + `RefreshDatabase`, tidak menyentuh database development.

## Struktur tabel `couriers`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string(100), index | |
| phone | string(20), unique | format Indonesia (08xx / 628xx / +628xx) |
| email | string, nullable, unique | |
| address | text, nullable | |
| vehicle_type | string | `motorcycle`, `car`, `bicycle`, `van` |
| vehicle_plate | string(15), nullable | |
| level | tinyint unsigned, index | **1-5**, default 1 |
| status | string, index | `active` (default), `inactive`, `suspended` |
| created_at / updated_at | timestamp | `created_at` = tanggal didaftarkan |

## Endpoint
Base URL `/api`. Kirim header `Accept: application/json`.

| Method | URL | Keterangan |
|---|---|---|
| GET | `/couriers` | daftar kurir (paginated) |
| POST | `/couriers` | tambah kurir, `201` |
| GET | `/couriers/{id}` | detail kurir (semua data) |
| PUT | `/couriers/{id}` | ubah seluruh data (field wajib harus dikirim) |
| PATCH | `/couriers/{id}` | ubah sebagian field |
| DELETE | `/couriers/{id}` | hapus kurir, `204` |

### Query parameter `GET /couriers`
| Param | Contoh | Default | Keterangan |
|---|---|---|---|
| `search` | `?search=budi+agung` | - | Semua kata harus ada di nama, urutan bebas. "budi agung" cocok dengan "Budiono Hadi Agung" |
| `level` | `?level=2,3` | semua | Filter level, dipisah koma (1-5) |
| `sort` | `?sort=created_at` | `name` | `name` atau `created_at` (tanggal didaftarkan) |
| `direction` | `?direction=desc` | `asc` | `asc` / `desc` |
| `per_page` | `?per_page=25` | 15 | 1-100 |
| `page` | `?page=2` | 1 | |

Contoh: `GET /api/couriers?search=budi&level=2,3&sort=created_at&direction=desc&per_page=10`

Contoh response:
```json
{
  "data": [
    {
      "id": 1, "name": "Budiono Hadi Agung", "phone": "081234567890",
      "email": "budi@example.com", "address": "Jl. Raya Darmo No. 1",
      "vehicle_type": "motorcycle", "vehicle_plate": "L 1234 AB",
      "level": 3, "status": "active",
      "created_at": "2026-11-24T08:00:00+00:00", "updated_at": "2026-11-24T08:00:00+00:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```
Error validasi mengembalikan `422` dengan format standar Laravel (`message`, `errors`).

## Keputusan desain
- **Form Request** terpisah (`Index/Store/Update`): validasi tidak menumpuk di controller.
- **Query scope** di model (`search`, `withLevels`, `sortBy`): controller tipis dan logika mudah dites.
- **API Resource**: format response konsisten dan terpisah dari struktur tabel.
- **Enum PHP** untuk `vehicle_type` dan `status`: nilai valid terpusat.
- **Whitelist sorting** mencegah sorting ke kolom sembarang; `id` dipakai sebagai tie-breaker agar pagination stabil.
- Karakter wildcard `%` dan `_` pada `search` dibuang agar input tidak menjadi pola LIKE.
- Hard delete, sesuai brief (data hilang dari database).
