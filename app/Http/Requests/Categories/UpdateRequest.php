<?php

namespace App\Http\Requests\Categories;

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
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($category) {
                    return $query->where('name', $this->name)
                        ->where('type', $this->type ?? $category->type)
                        ->where('id', '!=', $category->id);
                }),
            ],
            'description' => 'nullable|string',
            'type' => 'required|in:article,project',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'name.unique' => 'Nama kategori dengan tipe yang sama sudah digunakan.',

            'description.string' => 'Deskripsi kategori harus berupa teks.',

            'type.required' => 'Tipe kategori wajib diisi.',
            'type.in' => 'Tipe kategori harus berupa article atau project.',
        ];
    }
}
