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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'demo_url' => 'nullable|url',
            'project_url' => 'nullable|url',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul project wajib diisi.',
            'title.string' => 'Judul project harus berupa teks.',
            'title.max' => 'Judul project maksimal 255 karakter.',

            'description.required' => 'Deskripsi project wajib diisi.',
            'description.string' => 'Deskripsi project harus berupa teks.',

            'content.required' => 'Konten project wajib diisi.',
            'content.string' => 'Konten project harus berupa teks.',

            'thumbnail.image' => 'File harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau webp.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB.',

            'demo_url.url' => 'URL demo tidak valid.',

            'project_url.url' => 'URL project tidak valid.',

            'status.required' => 'Status project wajib diisi.',
            'status.in' => 'Status project harus berupa draft atau published.',

            'published_at.date' => 'Tanggal publish tidak valid.',
        ];
    }
}
