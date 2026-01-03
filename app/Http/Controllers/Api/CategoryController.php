<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreRequest;
use App\Http\Requests\Categories\UpdateRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function projectCategories()
    {
        $locale = app()->getLocale();
        $name = 'name_' . $locale;
        $slug = 'slug_' . $locale;

        $categories = Category::select('id', $name, $slug)
            ->byLocale($locale)
            ->where('type', 'project')
            ->orderBy($name)->get();

        return ApiResponse::success($categories, __('messages.categories.list_success'));
    }

    public function articleCategories()
    {
        $locale = app()->getLocale();
        $name = 'name_' . $locale;
        $slug = 'slug_' . $locale;

        $categories = Category::select('id', $name, $slug)
            ->byLocale($locale)
            ->where('type', 'article')
            ->orderBy($name)->get();

        return ApiResponse::success($categories, __('messages.categories.list_success'));
    }

    public function store(StoreRequest $request)
    {
        try {
            $category = Category::create($request->validated());

            return ApiResponse::created($category, __('messages.categories.store_success'), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage(), [
                'name' => $request->name,
                'type' => $request->type,
            ]);

            return ApiResponse::serverError(__('messages.categories.store_failed'), 500);
        }
    }

    public function edit(Category $category)
    {
        $category = [
            'id' => $category->id,
            'type' => $category->type,
            'translations' => [
                'id' => [
                    'name' => $category->name_id,
                ],
                'en' => [
                    'name' => $category->name_en,
                ],
            ],
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];

        return ApiResponse::success($category, __('messages.categories.translations_success'));
    }

    public function update(UpdateRequest $request, Category $category)
    {
        try {
            $category->update($request->validated());

            return ApiResponse::updated($category, __('messages.categories.update_success'));
        } catch (\Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage(), [
                'id' => $category->id,
                'name' => $request->name,
                'type' => $request->type,
            ]);

            return ApiResponse::serverError(__('messages.categories.update_failed'), 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
            $projectsCount = $category->projects()->count();
            $articlesCount = $category->articles()->count();

            if ($projectsCount > 0 || $articlesCount > 0) {
                return ApiResponse::conflict(
                    __('messages.general.relation_constraint'),
                    400
                );
            }

            $category->delete();

            return ApiResponse::deleted(__('messages.categories.delete_success'));
        } catch (\Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage(), [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ]);

            return ApiResponse::serverError(__('messages.categories.delete_failed'), 500);
        }
    }
}
