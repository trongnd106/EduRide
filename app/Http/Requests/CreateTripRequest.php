<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'assistant_id' => [
                'nullable',
                'integer',
                'exists:drivers,id',
                'different:driver_id',
            ],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'total_students' => ['nullable', 'integer', 'min:0'],
            'curr_students' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'integer', Rule::in([0, 1])],
            'status' => ['nullable', 'integer', Rule::in([0, 1, 2])],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
        ];
    }
}
