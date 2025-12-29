<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\ApiRequest;

class UpdateProfileRequest extends ApiRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'instagram' => ['nullable', 'string', 'max:255', 'url'],
            'github' => ['nullable', 'string', 'max:255', 'url'],
            'linkedin' => ['nullable', 'string', 'max:255', 'url'],
            'facebook' => ['nullable', 'string', 'max:255', 'url'],
            'twitter' => ['nullable', 'string', 'max:255', 'url'],
        ];
    }

    public function messages(): array
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

            'avatar.image' => 'Avatar harus berupa gambar.',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',

            'bio.string' => 'Bio tidak valid.',
            'bio.max' => 'Bio maksimal 1000 karakter.',

            'instagram.string' => 'Instagram tidak valid.',
            'instagram.max' => 'Instagram maksimal 255 karakter.',
            'instagram.url' => 'Instagram harus berupa URL yang valid.',

            'github.string' => 'GitHub tidak valid.',
            'github.max' => 'GitHub maksimal 255 karakter.',
            'github.url' => 'GitHub harus berupa URL yang valid.',

            'linkedin.string' => 'LinkedIn tidak valid.',
            'linkedin.max' => 'LinkedIn maksimal 255 karakter.',
            'linkedin.url' => 'LinkedIn harus berupa URL yang valid.',

            'facebook.string' => 'Facebook tidak valid.',
            'facebook.max' => 'Facebook maksimal 255 karakter.',
            'facebook.url' => 'Facebook harus berupa URL yang valid.',

            'twitter.string' => 'Twitter tidak valid.',
            'twitter.max' => 'Twitter maksimal 255 karakter.',
            'twitter.url' => 'Twitter harus berupa URL yang valid.',
        ];
    }
}
