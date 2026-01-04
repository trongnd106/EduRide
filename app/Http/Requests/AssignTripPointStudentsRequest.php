<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTripPointStudentsRequest extends FormRequest
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
            'points' => ['required', 'array', 'min:1'],
            'points.*.id' => ['required', 'integer', 'exists:points,id'],
            'points.*.students' => ['required', 'array', 'min:1'],
            'points.*.students.*' => ['required', 'integer', 'exists:students,id', 'distinct'],
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
            'points.required' => 'Danh sách điểm là bắt buộc',
            'points.array' => 'Danh sách điểm phải là mảng',
            'points.min' => 'Phải có ít nhất 1 điểm',
            'points.*.id.required' => 'ID điểm là bắt buộc',
            'points.*.id.integer' => 'ID điểm phải là số nguyên',
            'points.*.id.exists' => 'Điểm không tồn tại',
            'points.*.students.required' => 'Danh sách học sinh là bắt buộc',
            'points.*.students.array' => 'Danh sách học sinh phải là mảng',
            'points.*.students.min' => 'Mỗi điểm phải có ít nhất 1 học sinh',
            'points.*.students.*.required' => 'ID học sinh không được để trống',
            'points.*.students.*.integer' => 'ID học sinh phải là số nguyên',
            'points.*.students.*.exists' => 'Học sinh không tồn tại',
            'points.*.students.*.distinct' => 'Có học sinh bị trùng lặp trong danh sách',
        ];
    }
}

