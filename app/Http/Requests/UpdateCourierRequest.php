<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * PUT   = semua field wajib dikirim (replace).
     * PATCH = hanya field yang dikirim yang divalidasi (partial).
     */
    public function rules(): array
    {
        $presence = $this->isMethod('PATCH') ? 'sometimes' : 'required';
        $courier = $this->route('courier');

        return [
            'name' => [$presence, 'string', 'min:2', 'max:100'],
            'phone' => [
                $presence, 'string', 'regex:'.StoreCourierRequest::PHONE_REGEX,
                Rule::unique('couriers', 'phone')->ignore($courier),
            ],
            'email' => ['nullable', 'email:rfc', 'max:255', Rule::unique('couriers', 'email')->ignore($courier)],
            'address' => ['nullable', 'string', 'max:1000'],
            'vehicle_type' => [$presence, Rule::enum(VehicleType::class)],
            'vehicle_plate' => ['nullable', 'string', 'max:15'],
            'level' => [$presence, 'integer', 'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL],
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
