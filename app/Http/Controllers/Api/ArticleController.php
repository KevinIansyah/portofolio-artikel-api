<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\StoreRequest;
use App\Http\Requests\Articles\UpdateRequest;
use App\Models\Article;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $fallbackLocale = $locale === 'id' ? 'en' : 'id';
        $categorySlug = 'slug_' . $locale;
        $categorySlugFallback = 'slug_' . $fallbackLocale;
        $tagSlug = 'slug_' . $locale;
        $tagSlugFallback = 'slug_' . $fallbackLocale;

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 30, 40, 50]) ? $perPage : 20;

        $search = $request->get('search');

        $query = Article::byLocale($locale)
            ->with(['categories:id,name_id,name_en,slug_id,slug_en', 'tags:id,name_id,name_en,slug_id,slug_en', 'user:id,name']);

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($categorySlug, $categorySlugFallback, $request) {
                $q->where($categorySlug, $request->category)
                    ->orWhere($categorySlugFallback, $request->category);
            });
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($tagSlug, $tagSlugFallback, $request) {
                $q->where($tagSlug, $request->tag)
                    ->orWhere($tagSlugFallback, $request->tag);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title_id', 'like', $search . '%')
                    ->orWhere('title_en', 'like', $search . '%');
            });
        }

        $articles = $query->latest('published_at')
            ->paginate($request->get('per_page', $perPage));

        return ApiResponse::paginated($articles, __('messages.articles.list_success'));
    }

    // public function index(Request $request)
    // {
    //     $locale = app()->getLocale();
    //     $categorySlug = 'slug_' . $locale;
    //     $tagSlug = 'slug_' . $locale;

    //     $perPage = $request->get('per_page', 20);
    //     $perPage = in_array($perPage, [20, 30, 40, 50]) ? $perPage : 20;

    //     $search = $request->get('search');

    //     $query = Article::byLocale($locale)
    //         ->with(['categories:id,name_id,name_en,slug_id,slug_en', 'tags:id,name_id,name_en,slug_id,slug_en', 'user:id,name']);

    //     if ($request->has('category')) {
    //         $query->whereHas('categories', function ($q) use ($categorySlug, $request) {
    //             $q->where($categorySlug, $request->category);
    //         });
    //     }

    //     if ($request->has('tag')) {
    //         $query->whereHas('tags', function ($q) use ($tagSlug, $request) {
    //             $q->where($tagSlug, $request->tag);
    //         });
    //     }

    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('title_id', 'like', $search . '%')
    //                 ->orWhere('title_en', 'like', $search . '%');
    //         });
    //     }

    //     $articles = $query->latest('published_at')
    //         ->paginate($request->get('per_page', $perPage));

    //     return ApiResponse::paginated($articles, __('messages.articles.list_success'));
    // }

    public function show($slug)
    {
        $locale = app()->getLocale();
        $fallbackLocale = $locale === 'id' ? 'en' : 'id';

        $article = Article::where('status', 'published')
            ->where(function ($query) use ($slug, $locale, $fallbackLocale) {
                $query->where("slug_{$locale}", $slug)
                    ->orWhere("slug_{$fallbackLocale}", $slug);
            })
            ->with(['categories:id,name_id,name_en,slug_id,slug_en', 'tags:id,name_id,name_en,slug_id,slug_en', 'user:id,name,email,avatar_url'])
            ->firstOrFail();

        $article->increment('views');

        return ApiResponse::success($article, __('messages.articles.detail_success'));
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = $request->user()->id;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail_url'] = $this->imageService->upload(
                    file: $request->file('thumbnail'),
                    name: $data['title_id'],
                    folder: 'articles'
                );
            }

            if ($data['status'] === 'published') {
                $data['published_at'] = now();
            }

            $article = Article::create($data);

            if ($request->has('categories')) {
                $article->categories()->sync($request->categories);
            }

            if ($request->has('tags')) {
                $article->tags()->sync($request->tags);
            }


            DB::commit();

            $article->load(['categories:id,name_id,name_en,slug_id,slug_en', 'tags:id,name_id,name_en,slug_id,slug_en', 'user:id,name']);

            return ApiResponse::created($article, __('messages.articles.store_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to store article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'title_id' => $request->title_id ?? null,
            ]);

            return ApiResponse::serverError(__('messages.articles.store_failed'), 500);
        }
    }

    public function edit(Article $article)
    {
        $article = [
            'id' => $article->id,
            'user_id' => $article->user_id,
            'thumbnail_url' => $article->thumbnail_url,
            'status' => $article->status,
            'views' => $article->views,
            'reading_time' => $article->reading_time,
            'published_at' => $article->published_at,
            'translations' => [
                'id' => [
                    'title' => $article->title_id,
                    'description' => $article->description_id,
                    'content' => $article->content_id,
                ],
                'en' => [
                    'title' => $article->title_en,
                    'description' => $article->description_en,
                    'content' => $article->content_en,
                ],
            ],
            'categories' => $article->categories,
            'tags' => $article->tags,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
        ];

        return ApiResponse::success($article, __('messages.articles.edit_success'));
    }

    public function update(UpdateRequest $request, Article $article)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail_url'] = $this->imageService->update(
                    oldUrl: $article->thumbnail_url,
                    newFile: $request->file('thumbnail'),
                    name: $data['title_id'],
                    folder: 'articles'
                );
            }

            if ($data['status'] === 'published' && $article->status === 'draft') {
                $data['published_at'] = now();
            }

            if ($data['status'] === 'draft' && $article->status === 'published') {
                $data['published_at'] = null;
            }

            $article->update($data);

            if ($request->has('categories')) {
                $article->categories()->sync($request->categories);
            }

            if ($request->has('tags')) {
                $article->tags()->sync($request->tags);
            }

            DB::commit();

            $article->load(['categories:id,name_id,name_en,slug_id,slug_en', 'tags:id,name_id,name_en,slug_id,slug_en', 'user:id,name']);

            return ApiResponse::updated($article, __('messages.articles.update_success'));
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ]);

            return ApiResponse::serverError(__('messages.articles.update_failed'), 500);
        }
    }

    public function destroy(Request $request, Article $article)
    {
        try {
            DB::beginTransaction();

            if ($article->thumbnail_url) {
                $this->imageService->delete($article->thumbnail_url);
            }

            $article->categories()->detach();
            $article->tags()->detach();

            $article->delete();

            DB::commit();

            return ApiResponse::deleted(__('messages.articles.delete_success'));
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ]);

            return ApiResponse::serverError(__('messages.articles.delete_failed'), 500);
        }
    }
}
