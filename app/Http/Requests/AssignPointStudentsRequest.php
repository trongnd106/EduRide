<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignPointStudentsRequest extends FormRequest
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
            'points.*.point_id' => ['required', 'integer', 'exists:points,id'],
            'points.*.student_ids' => ['required', 'array', 'min:1'],
            'points.*.student_ids.*' => ['required', 'integer', 'exists:students,id', 'distinct'],
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
            'points.*.point_id.required' => 'ID điểm là bắt buộc',
            'points.*.point_id.integer' => 'ID điểm phải là số nguyên',
            'points.*.point_id.exists' => 'Điểm không tồn tại',
            'points.*.student_ids.required' => 'Danh sách học sinh là bắt buộc',
            'points.*.student_ids.array' => 'Danh sách học sinh phải là mảng',
            'points.*.student_ids.min' => 'Mỗi điểm phải có ít nhất 1 học sinh',
            'points.*.student_ids.*.required' => 'ID học sinh không được để trống',
            'points.*.student_ids.*.integer' => 'ID học sinh phải là số nguyên',
            'points.*.student_ids.*.exists' => 'Học sinh không tồn tại',
            'points.*.student_ids.*.distinct' => 'Có học sinh bị trùng lặp trong danh sách',
        ];
    }
}

