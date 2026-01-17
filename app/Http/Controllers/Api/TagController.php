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
    public function index()
    {
        $locale = app()->getLocale();
        $name = 'name_' . $locale;
        $slug = 'slug_' . $locale;

        $tags = Tag::select('id', $name, $slug)
            ->orderBy($name)
            ->get();

        return ApiResponse::success($tags, __('messages.tags.list_success'));
    }

    public function indexPaginated(Request $request)
    {
        $locale = app()->getLocale();
        $name = 'name_' . $locale;
        $slug = 'slug_' . $locale;

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 30, 40, 50]) ? $perPage : 20;

        $search = $request->get('search');

        $query = Tag::select('id', $name, $slug);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_id', 'like', $search . '%')
                    ->orWhere('name_en', 'like', $search . '%');
            });
        }

        $skills = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', $perPage));

        return ApiResponse::paginated($skills, __('messages.skills.list_success'));
    }

    public function edit(Tag $tag)
    {
        $tag = [
            'id' => $tag->id,
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
        ];

        return ApiResponse::success($tag, __('messages.tags.translations_success'));
    }

    public function store(StoreRequest $request)
    {
        try {
            $tag = Tag::create($request->validated());

            return ApiResponse::created($tag, __('messages.tags.store_success'), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create tag: ' . $e->getMessage(), [
                'name' => $request->name,
            ]);

            return ApiResponse::serverError(__('messages.tags.store_failed'), 500);
        }
    }

    public function update(UpdateRequest $request, Tag $tag)
    {
        try {
            $tag->update($request->validated());

            return ApiResponse::updated($tag, __('messages.tags.update_success'));
        } catch (\Exception $e) {
            Log::error('Failed to update tag: ' . $e->getMessage(), [
                'id' => $tag->id,
                'name' => $request->name,
            ]);

            return ApiResponse::serverError(__('messages.tags.update_failed'), 500);
        }
    }

    public function destroy(Tag $tag)
    {
        try {
            $articlesCount = $tag->articles()->count();

            if ($articlesCount > 0) {
                return ApiResponse::conflict(
                    __('messages.general.relation_constraint'),
                    400
                );
            }

            $tag->delete();

            return ApiResponse::deleted(__('messages.tags.delete_success'));
        } catch (\Exception $e) {
            Log::error('Failed to delete tag: ' . $e->getMessage(), [
                'id' => $tag->id,
                'name' => $tag->name,
            ]);

            return ApiResponse::serverError(__('messages.tags.delete_failed'), 500);
        }
    }
}
