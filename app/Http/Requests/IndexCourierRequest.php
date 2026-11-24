<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCourierRequest extends FormRequest
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            // contoh: ?level=2,3
            'level' => ['nullable', 'string', 'regex:/^[1-5](,[1-5])*$/'],
            'sort' => ['nullable', Rule::in(Courier::SORTABLE_FIELDS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,'.self::MAX_PER_PAGE],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'level.regex' => 'Format level harus angka 1-5 dipisah koma, contoh: 2,3.',
        ];
    }

    /**
     * @return array<int, int>
     */
    public function levels(): array
    {
        $level = $this->validated('level');

        if ($level === null) {
            return [];
        }

        return array_values(array_unique(array_map('intval', explode(',', $level))));
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? self::DEFAULT_PER_PAGE);
    }
}
