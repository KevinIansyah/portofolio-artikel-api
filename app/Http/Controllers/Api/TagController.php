<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tags\StoreRequest;
use App\Http\Requests\Tags\UpdateRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function projectTags()
    {
        $locale = app()->getLocale();
        $name = 'name_' . $locale;
        $slug = 'slug_' . $locale;

        $tags = Tag::select('id', $name, $slug)
            ->where('type', 'project')
            ->orderBy($name)
            ->get();

        return ApiResponse::success($tags, __('messages.tags.list_success'));
    }

    public function articleTags()
    {
        $locale = app()->getLocale();
        $name = 'name_' . $locale;
        $slug = 'slug_' . $locale;

        $tags = Tag::select('id', $name, $slug)
            ->where('type', 'article')
            ->orderBy($name)
            ->get();

        return ApiResponse::success($tags, __('messages.tags.list_success'));
    }


    public function store(StoreRequest $request)
    {
        try {
            $tag = Tag::create($request->validated());

            return ApiResponse::success($tag, __('messages.tags.store_success'), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create tag: ' . $e->getMessage(), [
                'name' => $request->name,
                'type' => $request->type,
            ]);

            return ApiResponse::error(__('messages.tags.store_failed'), 500);
        }
    }

    public function update(UpdateRequest $request, Tag $tag)
    {
        try {
            $tag->update($request->validated());

            return ApiResponse::success($tag, __('messages.tags.update_success'));
        } catch (\Exception $e) {
            Log::error('Failed to update tag: ' . $e->getMessage(), [
                'id' => $tag->id,
                'name' => $request->name,
                'type' => $request->type,
            ]);

            return ApiResponse::error(__('messages.tags.update_failed'), 500);
        }
    }

    public function destroy(Tag $tag)
    {
        try {
            $projectsCount = $tag->projects()->count();
            $articlesCount = $tag->articles()->count();

            if ($projectsCount > 0 || $articlesCount > 0) {
                return ApiResponse::error(
                    __('messages.general.relation_constraint'),
                    400
                );
            }

            $tag->delete();

            return ApiResponse::success(null, __('messages.tags.delete_success'));
        } catch (\Exception $e) {
            Log::error('Failed to delete tag: ' . $e->getMessage(), [
                'id' => $tag->id,
                'name' => $tag->name,
                'type' => $tag->type,
            ]);

            return ApiResponse::error(__('messages.tags.delete_failed'), 500);
        }
    }

    public function translations(Tag $tag)
    {
        return ApiResponse::success([
            'id' => $tag->id,
            'type' => $tag->type,
            'translations' => [
                'id' => [
                    'name' => $tag->name_id,
                ],
                'en' => [
                    'name' => $tag->name_en,
                ],
            ],
            'created_at' => $tag->created_at,
            'updated_at' => $tag->updated_at,
        ], __('messages.tags.translations_success'));
    }
}
