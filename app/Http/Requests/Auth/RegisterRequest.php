<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class RegisterRequest extends ApiRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'en') {
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
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama tidak valid.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi tidak valid.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string'   => 'Name must be a valid string.',
            'name.max'      => 'Name may not be greater than 255 characters.',

            'email.required' => 'Email is required.',
            'email.string'   => 'Email must be a valid string.',
            'email.email'    => 'Email format is invalid.',
            'email.max'      => 'Email may not be greater than 255 characters.',
            'email.unique'   => 'Email has already been taken.',

            'password.required'   => 'Password is required.',
            'password.string'     => 'Password must be a valid string.',
            'password.min'        => 'Password must be at least 8 characters.',
            'password.confirmed'  => 'Password confirmation does not match.',

        ];
    }
}
