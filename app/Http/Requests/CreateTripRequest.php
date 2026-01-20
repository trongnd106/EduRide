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
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],

            'is_mon' => ['nullable', 'boolean'],
            'is_tue' => ['nullable', 'boolean'],
            'is_wed' => ['nullable', 'boolean'],
            'is_thu' => ['nullable', 'boolean'],
            'is_fri' => ['nullable', 'boolean'],
            'is_sat' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Tên lộ trình phải là chuỗi ký tự.',
            'name.max' => 'Tên lộ trình không được vượt quá 255 ký tự.',
            'driver_id.exists' => 'Tài xế không tồn tại.',
            'assistant_id.exists' => 'Phụ xe không tồn tại.',
            'assistant_id.different' => 'Phụ xe không thể trùng với tài xế.',
            'vehicle_id.exists' => 'Xe không tồn tại.',
            'total_students.integer' => 'Tổng số học sinh phải là số nguyên.',
            'total_students.min' => 'Tổng số học sinh không được nhỏ hơn 0.',
            'curr_students.integer' => 'Số học sinh hiện tại phải là số nguyên.',
            'curr_students.min' => 'Số học sinh hiện tại không được nhỏ hơn 0.',
            'type.in' => 'Loại chuyến xe không hợp lệ. Chỉ chấp nhận 0 (đón) hoặc 1 (trả).',
            'status.in' => 'Trạng thái không hợp lệ.',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'is_mon.boolean' => 'Thứ 2 phải là giá trị boolean.',
            'is_tue.boolean' => 'Thứ 3 phải là giá trị boolean.',
            'is_wed.boolean' => 'Thứ 4 phải là giá trị boolean.',
            'is_thu.boolean' => 'Thứ 5 phải là giá trị boolean.',
            'is_fri.boolean' => 'Thứ 6 phải là giá trị boolean.',
            'is_sat.boolean' => 'Thứ 7 phải là giá trị boolean.',
        ];
    }

    /**
     * Prepare the data for validation.
     * Convert boolean strings to actual booleans.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_mon' => $this->convertToBoolean($this->input('is_mon', false)),
            'is_tue' => $this->convertToBoolean($this->input('is_tue', false)),
            'is_wed' => $this->convertToBoolean($this->input('is_wed', false)),
            'is_thu' => $this->convertToBoolean($this->input('is_thu', false)),
            'is_fri' => $this->convertToBoolean($this->input('is_fri', false)),
            'is_sat' => $this->convertToBoolean($this->input('is_sat', false)),
        ]);
    }

    /**
     * Convert various boolean representations to actual boolean.
     *
     * @param mixed $value
     * @return bool
     */
    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return false;
    }

    /**
     * Get validated data with proper defaults for days of week.
     *
     * @return array
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Ensure all days of week have boolean values
        $validated['is_mon'] = $validated['is_mon'] ?? false;
        $validated['is_tue'] = $validated['is_tue'] ?? false;
        $validated['is_wed'] = $validated['is_wed'] ?? false;
        $validated['is_thu'] = $validated['is_thu'] ?? false;
        $validated['is_fri'] = $validated['is_fri'] ?? false;
        $validated['is_sat'] = $validated['is_sat'] ?? false;

        return $validated;
    }

    /**
     * Additional validation rules.
     * At least one day of week must be selected.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasAtLeastOneDay =
                $this->input('is_mon', false) ||
                $this->input('is_tue', false) ||
                $this->input('is_wed', false) ||
                $this->input('is_thu', false) ||
                $this->input('is_fri', false) ||
                $this->input('is_sat', false);

            if (!$hasAtLeastOneDay) {
                $validator->errors()->add(
                    'days_of_week',
                    'Phải chọn ít nhất một ngày trong tuần cho chuyến xe.'
                );
            }
        });
    }
}
