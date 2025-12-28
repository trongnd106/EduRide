<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignStudentsRequest extends FormRequest
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
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id', 'distinct'],
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
            'trip_id.required' => 'Trip ID là bắt buộc',
            'trip_id.exists' => 'Trip không tồn tại',
            'student_ids.required' => 'Danh sách học sinh là bắt buộc',
            'student_ids.array' => 'Danh sách học sinh phải là mảng',
            'student_ids.min' => 'Phải có ít nhất 1 học sinh',
            'student_ids.*.required' => 'ID học sinh không được để trống',
            'student_ids.*.integer' => 'ID học sinh phải là số nguyên',
            'student_ids.*.exists' => 'Học sinh không tồn tại',
            'student_ids.*.distinct' => 'Có học sinh bị trùng lặp trong danh sách',
        ];
    }
}

