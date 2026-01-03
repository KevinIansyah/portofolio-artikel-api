<?php

namespace App\Http\Requests\Skill;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $skill = $this->route('skill');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('skills', 'name')->ignore($skill->id),
            ],
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
            'name.required' => 'Nama kemampuan wajib diisi.',
            'name.string' => 'Nama kemampuan harus berupa string.',
            'name.max' => 'Nama kemampuan tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Nama kemampuan sudah digunakan',

            'dark_icon.image' => 'File harus berupa gambar.',
            'dark_icon.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau webp.',
            'dark_icon.max' => 'Ukuran gambar maksimal 2MB.',

            'light_icon.image' => 'File harus berupa gambar.',
            'light_icon.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau webp.',
            'light_icon.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            'name.required' => 'Skill name is required.',
            'name.string' => 'Skill name must be a string.',
            'name.max' => 'Skill name may not be greater than 255 characters.',
            'name.unique' => 'Skill name has already been taken',

            'dark_icon.image' => 'File must be an image.',
            'dark_icon.mimes' => 'Image format must be jpeg, png, jpg, gif, or webp.',
            'dark_icon.max' => 'Maximum image size is 2MB.',

            'light_icon.image' => 'File must be an image.',
            'light_icon.mimes' => 'Image format must be jpeg, png, jpg, gif, or webp.',
            'light_icon.max' => 'Maximum image size is 2MB.',
        ];
    }
}
