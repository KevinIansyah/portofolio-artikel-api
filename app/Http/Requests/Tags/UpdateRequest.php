<?php

namespace App\Http\Requests\Tags;

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
        $tag = $this->route('tag');

        return [
            'name_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where(function ($query) use ($tag) {
                    return $query->where('name_id', $this->name_id)
                        ->where('type', $this->type ?? $tag->type)
                        ->where('id', '!=', $tag->id);
                }),
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where(function ($query) use ($tag) {
                    return $query->where('name_en', $this->name_en)
                        ->where('type', $this->type ?? $tag->type)
                        ->where('id', '!=', $tag->id);
                }),
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
            'name_id.required' => 'Nama label (ID) wajib diisi.',
            'name_id.string' => 'Nama label (ID) harus berupa string.',
            'name_id.max' => 'Nama label (ID) tidak boleh lebih dari 255 karakter.',
            'name_id.unique' => 'Nama label (ID) sudah digunakan.',

            'name_en.required' => 'Nama label (EN) wajib diisi.',
            'name_en.string' => 'Nama label (EN) harus berupa string.',
            'name_en.max' => 'Nama label (EN) tidak boleh lebih dari 255 karakter.',
            'name_en.unique' => 'Nama label (EN) sudah digunakan.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            'name_id.required' => 'Tag name (ID) is required.',
            'name_id.string' => 'Tag name (ID) must be a string.',
            'name_id.max' => 'Tag name (ID) may not be greater than 255 characters.',
            'name_id.unique' => 'Tag name (ID) has already been taken.',

            'name_en.required' => 'Tag name (EN) is required.',
            'name_en.string' => 'Tag name (EN) must be a string.',
            'name_en.max' => 'Tag name (EN) may not be greater than 255 characters.',
            'name_en.unique' => 'Tag name (EN) has already been taken.',
        ];
    }
}
