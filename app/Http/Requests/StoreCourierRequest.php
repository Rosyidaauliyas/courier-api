<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
{
    /** Nomor HP Indonesia: 08xx / 628xx / +628xx */
    public const PHONE_REGEX = '/^(\+62|62|0)8[1-9][0-9]{6,11}$/';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_REGEX, Rule::unique('couriers', 'phone')],
            'email' => ['nullable', 'email:rfc', 'max:255', Rule::unique('couriers', 'email')],
            'address' => ['nullable', 'string', 'max:1000'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'vehicle_plate' => ['nullable', 'string', 'max:15'],
            'level' => ['required', 'integer', 'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL],
            'status' => ['sometimes', Rule::enum(CourierStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Format nomor telepon tidak valid (contoh: 081234567890).',
            'level.between' => 'Level harus bernilai '.Courier::MIN_LEVEL.' sampai '.Courier::MAX_LEVEL.'.',
        ];
    }
}
