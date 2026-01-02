<?php

namespace App\Http\Requests;

use App\Rules\NoSpaces;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email', 'max:50', new NoSpaces],
            'password' => ['required', 'string', 'max:50']
        ];
    }

    public function attributes()
    {
        return [
            'email' => 'Địa chỉ email',
            'password' => 'Mật khẩu'
        ];
    }
}
