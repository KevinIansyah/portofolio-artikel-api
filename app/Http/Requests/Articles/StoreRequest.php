<?php

namespace App\Http\Requests\Articles;

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
            'title_id.required' => 'Judul artikel (ID) wajib diisi.',
            'title_id.string' => 'Judul artikel (ID) harus berupa teks.',
            'title_id.max' => 'Judul artikel (ID) maksimal 255 karakter.',

            'slug_id.unique' => 'Slug (ID) sudah digunakan.',

            'description_id.required' => 'Deskripsi artikel (ID) wajib diisi.',
            'description_id.string' => 'Deskripsi artikel (ID) harus berupa teks.',

            'content_id.required' => 'Konten artikel (ID) wajib diisi.',
            'content_id.string' => 'Konten artikel (ID) harus berupa teks.',

            // English fields
            'title_en.required' => 'Judul artikel (EN) wajib diisi.',
            'title_en.string' => 'Judul artikel (EN) harus berupa teks.',
            'title_en.max' => 'Judul artikel (EN) maksimal 255 karakter.',

            'slug_en.unique' => 'Slug (EN) sudah digunakan.',

            'description_en.required' => 'Deskripsi artikel (EN) wajib diisi.',
            'description_en.string' => 'Deskripsi artikel (EN) harus berupa teks.',

            'content_en.required' => 'Konten artikel (EN) wajib diisi.',
            'content_en.string' => 'Konten artikel (EN) harus berupa teks.',

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
            'title_id.required' => 'Article title (ID) is required.',
            'title_id.string' => 'Article title (ID) must be a string.',
            'title_id.max' => 'Article title (ID) maximum 255 characters.',

            'slug_id.unique' => 'Slug (ID) already taken.',

            'description_id.required' => 'Article description (ID) is required.',
            'description_id.string' => 'Article description (ID) must be a string.',

            'content_id.required' => 'Article content (ID) is required.',
            'content_id.string' => 'Article content (ID) must be a string.',

            // English fields
            'title_en.required' => 'Article title (EN) is required.',
            'title_en.string' => 'Article title (EN) must be a string.',
            'title_en.max' => 'Article title (EN) maximum 255 characters.',

            'slug_en.unique' => 'Slug (EN) already taken.',

            'description_en.required' => 'Article description (EN) is required.',
            'description_en.string' => 'Article description (EN) must be a string.',

            'content_en.required' => 'Article content (EN) is required.',
            'content_en.string' => 'Article content (EN) must be a string.',

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
