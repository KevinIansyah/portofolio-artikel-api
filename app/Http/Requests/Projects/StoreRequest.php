<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

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
            'title_id' => 'required|string|max:255',
            'slug_id' => 'nullable|string|max:255|unique:articles,slug_id',
            'description_id' => 'required|string',
            'content_id' => 'required|string',

            'title_en' => 'required|string|max:255',
            'slug_en' => 'nullable|string|max:255|unique:articles,slug_en',
            'description_en' => 'required|string',
            'content_en' => 'required|string',
            
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|exists:categories,id',
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
            // Indonesian fields
            'title_id.required' => 'Judul artikel (Indonesia) wajib diisi.',
            'title_id.string' => 'Judul artikel (Indonesia) harus berupa teks.',
            'title_id.max' => 'Judul artikel (Indonesia) maksimal 255 karakter.',

            'slug_id.unique' => 'Slug (Indonesia) sudah digunakan.',

            'description_id.required' => 'Deskripsi artikel (Indonesia) wajib diisi.',
            'description_id.string' => 'Deskripsi artikel (Indonesia) harus berupa teks.',

            'content_id.required' => 'Konten artikel (Indonesia) wajib diisi.',
            'content_id.string' => 'Konten artikel (Indonesia) harus berupa teks.',

            // English fields
            'title_en.required' => 'Judul artikel (English) wajib diisi.',
            'title_en.string' => 'Judul artikel (English) harus berupa teks.',
            'title_en.max' => 'Judul artikel (English) maksimal 255 karakter.',

            'slug_en.unique' => 'Slug (English) sudah digunakan.',

            'description_en.required' => 'Deskripsi artikel (English) wajib diisi.',
            'description_en.string' => 'Deskripsi artikel (English) harus berupa teks.',

            'content_en.required' => 'Konten artikel (English) wajib diisi.',
            'content_en.string' => 'Konten artikel (English) harus berupa teks.',

            // Shared fields
            'thumbnail.image' => 'File harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau webp.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB.',

            'status.required' => 'Status artikel wajib diisi.',
            'status.in' => 'Status artikel harus berupa draft atau published.',


            'published_at.date' => 'Tanggal publish tidak valid.',

            'categories.required' => 'Kategori wajib dipilih minimal 1.',
            'categories.array' => 'Format kategori tidak valid.',
            'categories.min' => 'Pilih minimal 1 kategori.',
            'categories.*.required' => 'Kategori tidak valid.',
            'categories.*.exists' => 'Kategori tidak ditemukan.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            // Indonesian fields
            'title_id.required' => 'Article title (Indonesia) is required.',
            'title_id.string' => 'Article title (Indonesia) must be a string.',
            'title_id.max' => 'Article title (Indonesia) maximum 255 characters.',

            'slug_id.unique' => 'Slug (Indonesia) already taken.',

            'description_id.required' => 'Article description (Indonesia) is required.',
            'description_id.string' => 'Article description (Indonesia) must be a string.',

            'content_id.required' => 'Article content (Indonesia) is required.',
            'content_id.string' => 'Article content (Indonesia) must be a string.',

            // English fields
            'title_en.required' => 'Article title (English) is required.',
            'title_en.string' => 'Article title (English) must be a string.',
            'title_en.max' => 'Article title (English) maximum 255 characters.',

            'slug_en.unique' => 'Slug (English) already taken.',

            'description_en.required' => 'Article description (English) is required.',
            'description_en.string' => 'Article description (English) must be a string.',

            'content_en.required' => 'Article content (English) is required.',
            'content_en.string' => 'Article content (English) must be a string.',

            // Shared fields
            'thumbnail.image' => 'File must be an image.',
            'thumbnail.mimes' => 'Image format must be jpeg, png, jpg, gif, or webp.',
            'thumbnail.max' => 'Maximum image size is 2MB.',

            'status.required' => 'Article status is required.',
            'status.in' => 'Article status must be draft or published.',

            'published_at.date' => 'Publish date is invalid.',

            'categories.required' => 'At least 1 category is required.',
            'categories.array' => 'Invalid category format.',
            'categories.min' => 'Select at least 1 category.',
            'categories.*.required' => 'Category is invalid.',
            'categories.*.exists' => 'Category not found.',
        ];
    }
}
