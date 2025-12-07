<?php

namespace App\Http\Requests;

use App\Constants\AppConst;
use App\Rules\NoSpaces;
use Illuminate\Validation\Rule;
use function App\Http\Requests\Admin\__;

class UserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:50', new NoSpaces, $uniqueRule],
        ];
    }

    public function attributes()
    {
        // return __('validation.attributes.user_request');
    }
}
