<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class LoginRequest extends ApiRequest
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale == "en") {
            return $this->englishMessages();
        }

        return $this->indonesianMessages();
    }

    /**
     * Indonesian validation messages
     */
    private function indonesianMessages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa string.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa string.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.string' => 'Email must be a string.',
            'email.email' => 'Email must be a valid email address.',

            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ];
    }
}
