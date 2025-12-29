<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreRequest;
use App\Http\Requests\Categories\UpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function projectCategories()
    {

        $categories = Category::select('id', 'name', 'slug')->where('type', 'project')->withCount('projects')->orderBy('name')->get();

        return ApiResponse::success($categories, 'Daftar kategori project berhasil diambil');
    }

    public function articleCategories()
    {

        $categories = Category::select('id', 'name', 'slug')->where('type', 'article')->withCount('articles')->orderBy('name')->get();

        return ApiResponse::success($categories, 'Daftar kategori artikel berhasil diambil');
    }

    public function store(StoreRequest $request)
    {
        try {
            $category = Category::create($request->validated());

            return ApiResponse::success($category, 'Kategori berhasil dibuat', 201);
        } catch (\Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage(), [
                'name' => $request->name,
                'type' => $request->type,
            ]);

            return ApiResponse::error('Gagal membuat kategori', 500);
        }
    }

    public function update(UpdateRequest $request, Category $category)
    {
        try {
            $category->update($request->validated());

            return ApiResponse::success($category, 'Kategori berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage(), [
                'id' => $category->id,
                'name' => $request->name,
                'type' => $request->type,
            ]);

            return ApiResponse::error('Gagal memperbarui kategori', 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
            $projectsCount = $category->projects()->count();
            $articlesCount = $category->articles()->count();

            if ($projectsCount > 0 || $articlesCount > 0) {
                return ApiResponse::error(
                    "Kategori tidak dapat dihapus karena masih digunakan oleh {$projectsCount} project dan {$articlesCount} artikel",
                    400
                );
            }

            $category->delete();

            return ApiResponse::success(null, 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage(), [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ]);

            return ApiResponse::error('Gagal menghapus kategori', 500);
        }
    }
}
