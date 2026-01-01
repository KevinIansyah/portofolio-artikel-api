<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'name_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('name_id', $this->name_id)
                        ->where('type', $this->type);
                }),
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('name_en', $this->name_en)
                        ->where('type', $this->type);
                }),
            ],
            'type' => 'required|in:article,project',
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
            'name_id.required' => 'Nama kategori (ID) wajib diisi.',
            'name_id.string' => 'Nama kategori (ID) harus berupa string.',
            'name_id.max' => 'Nama kategori (ID) tidak boleh lebih dari 255 karakter.',
            'name_id.unique' => 'Nama kategori (ID) sudah digunakan untuk tipe kategori ini.',

            'name_en.required' => 'Nama kategori (EN) wajib diisi.',
            'name_en.string' => 'Nama kategori (EN) harus berupa string.',
            'name_en.max' => 'Nama kategori (EN) tidak boleh lebih dari 255 karakter.',
            'name_en.unique' => 'Nama kategori (EN) sudah digunakan untuk tipe kategori ini.',

            'type.required' => 'Tipe kategori wajib diisi.',
            'type.in' => 'Tipe kategori harus berupa article atau project.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            'name_id.required' => 'Category name (ID) is required.',
            'name_id.string' => 'Category name (ID) must be a string.',
            'name_id.max' => 'Category name (ID) may not be greater than 255 characters.',
            'name_id.unique' => 'Category name (ID) has already been taken for this category type.',

            'name_en.required' => 'Category name (EN) is required.',
            'name_en.string' => 'Category name (EN) must be a string.',
            'name_en.max' => 'Category name (EN) may not be greater than 255 characters.',
            'name_en.unique' => 'Category name (EN) has already been taken for this category type.',

            'type.required' => 'Category type is required.',
            'type.in' => 'Category type must be either article or project.',
        ];
    }
}
