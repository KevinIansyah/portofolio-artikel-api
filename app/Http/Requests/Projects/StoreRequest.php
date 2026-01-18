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
            'description_id' => 'required|string',
            'content_id' => 'required|string',

            'title_en' => 'required|string|max:255',
            'description_en' => 'required|string',
            'content_en' => 'required|string',

            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'demo_url' => 'nullable|url|max:255',
            'project_url' => 'nullable|url|max:255',

            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|exists:categories,id',
            'skills' => 'required|array|min:1',
            'skills.*' => 'required|exists:skills,id',
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
            'title_id.required' => 'Judul proyek (ID) wajib diisi.',
            'title_id.string' => 'Judul proyek (ID) harus berupa teks.',
            'title_id.max' => 'Judul proyek (ID) maksimal 255 karakter.',

            'description_id.required' => 'Deskripsi proyek (ID) wajib diisi.',
            'description_id.string' => 'Deskripsi proyek (ID) harus berupa teks.',

            'content_id.required' => 'Konten proyek (ID) wajib diisi.',
            'content_id.string' => 'Konten proyek (ID) harus berupa teks.',

            // English fields
            'title_en.required' => 'Judul proyek (EN) wajib diisi.',
            'title_en.string' => 'Judul proyek (EN) harus berupa teks.',
            'title_en.max' => 'Judul proyek (EN) maksimal 255 karakter.',

            'description_en.required' => 'Deskripsi proyek (EN) wajib diisi.',
            'description_en.string' => 'Deskripsi proyek (EN) harus berupa teks.',

            'content_en.required' => 'Konten proyek (EN) wajib diisi.',
            'content_en.string' => 'Konten proyek (EN) harus berupa teks.',

            // Shared fields
            'thumbnail.image' => 'File harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau webp.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB.',

            'status.required' => 'Status proyek wajib diisi.',
            'status.in' => 'Status proyek harus berupa draft atau published.',

            'published_at.date' => 'Tanggal publish tidak valid.',

            'project_url.url' => 'Format URL project tidak valid.',
            'project_url.max' => 'URL project maksimal 255 karakter.',

            'status.required' => 'Status proyek wajib diisi.',
            'status.in' => 'Status proyek harus berupa draft atau published.',

            'categories.required' => 'Kategori wajib dipilih minimal 1.',
            'categories.array' => 'Format kategori tidak valid.',
            'categories.min' => 'Pilih minimal 1 kategori.',
            'categories.*.required' => 'Kategori tidak valid.',
            'categories.*.exists' => 'Kategori tidak ditemukan.',

            'skills.required' => 'Skill wajib dipilih minimal 1.',
            'skills.array' => 'Format skill tidak valid.',
            'skills.min' => 'Pilih minimal 1 skill.',
            'skills.*.required' => 'Skill tidak valid.',
            'skills.*.exists' => 'Skill tidak ditemukan.',
        ];
    }

    /**
     * English validation messages
     */
    private function englishMessages(): array
    {
        return [
            // Indonesian fields
            'title_id.required' => 'Project title (ID) is required.',
            'title_id.string' => 'Project title (ID) must be a string.',
            'title_id.max' => 'Project title (ID) maximum 255 characters.',

            'slug_id.unique' => 'Slug (ID) already taken.',

            'description_id.required' => 'Project description (ID) is required.',
            'description_id.string' => 'Project description (ID) must be a string.',

            'content_id.required' => 'Project content (ID) is required.',
            'content_id.string' => 'Project content (ID) must be a string.',

            // English fields
            'title_en.required' => 'Project title (EN) is required.',
            'title_en.string' => 'Project title (EN) must be a string.',
            'title_en.max' => 'Project title (EN) maximum 255 characters.',

            'slug_en.unique' => 'Slug (EN) already taken.',

            'description_en.required' => 'Project description (EN) is required.',
            'description_en.string' => 'Project description (EN) must be a string.',

            'content_en.required' => 'Project content (EN) is required.',
            'content_en.string' => 'Project content (EN) must be a string.',

            // Shared fields
            'thumbnail.image' => 'File must be an image.',
            'thumbnail.mimes' => 'Image format must be jpeg, png, jpg, gif, or webp.',
            'thumbnail.max' => 'Maximum image size is 2MB.',

            'status.required' => 'Project status is required.',
            'status.in' => 'Project status must be draft or published.',

            'published_at.date' => 'Publish date is invalid.',

            'demo_url.url' => 'Demo URL format is invalid.',
            'demo_url.max' => 'Demo URL maximum 255 characters.',

            'project_url.url' => 'Project URL format is invalid.',
            'project_url.max' => 'Project URL maximum 255 characters.',

            'categories.required' => 'At least 1 category is required.',
            'categories.array' => 'Invalid category format.',
            'categories.min' => 'Select at least 1 category.',
            'categories.*.required' => 'Category is invalid.',
            'categories.*.exists' => 'Category not found.',

            'skills.required' => 'At least 1 skill is required.',
            'skills.array' => 'Invalid skill format.',
            'skills.min' => 'Select at least 1 skill.',
            'skills.*.required' => 'skill is invalid.',
            'skills.*.exists' => 'Skill not found.',
        ];
    }
}
