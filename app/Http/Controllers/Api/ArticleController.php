<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\StoreRequest;
use App\Http\Requests\Articles\UpdateRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $query = Article::where('status', 'published')
            ->byLocale($locale)
            ->with(['categories:id,name']);

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // if ($request->has('search')) {
        //     $query->search($request->search);
        // }

        $articles = $query->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return ApiResponse::paginated($articles, __('messages.articles.list_success'));
    }

    public function show($slug)
    {
        $locale = app()->getLocale();

        $article = Article::where("slug_{$locale}", $slug)
            ->where('status', 'published')
            ->with(['categories:id,name,slug', 'user:id,name,email,avatar'])
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
                $data['thumbnail_url'] = $this->uploadThumbnail(
                    $request->file('thumbnail'),
                    $request->title_id
                );
            }

            if ($data['status'] === 'published') {
                $data['published_at'] = now();
            }

            $article = Article::create($data);

            if ($request->has('categories')) {
                $article->categories()->sync($request->categories);
            }

            DB::commit();

            $article->load(['categories:id,name', 'user:id,name']);

            return ApiResponse::success($article, __('messages.articles.store_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to store article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'title_id' => $request->title_id ?? null,
            ]);

            return ApiResponse::error(__('messages.articles.store_failed'), 500);
        }
    }

    public function update(UpdateRequest $request, Article $article)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($request->hasFile('thumbnail')) {
                if ($article->thumbnail_url) {
                    $this->deleteThumbnail($article->thumbnail_url);
                }

                $data['thumbnail_url'] = $this->uploadThumbnail(
                    $request->file('thumbnail'),
                    $request->title_id ?? $article->title_id
                );
            }

            if ($data['status'] === 'published' && $article->published_at === null) {
                $data['published_at'] = now();
            }

            $article->update($data);

            if ($request->has('categories')) {
                $article->categories()->sync($request->categories);
            }

            DB::commit();

            $article->load(['categories:id,name', 'user:id,name']);

            return ApiResponse::success($article, __('messages.articles.update_success'));
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ]);

            return ApiResponse::error(__('messages.articles.update_failed'), 500);
        }
    }

    public function destroy(Request $request, Article $article)
    {
        try {
            DB::beginTransaction();

            if ($article->thumbnail_url) {
                $this->deleteThumbnail($article->thumbnail_url);
            }

            $article->categories()->detach();
            $article->delete();

            DB::commit();

            return ApiResponse::success(null, __('messages.articles.delete_success'));
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ]);

            return ApiResponse::error(__('messages.articles.delete_failed'), 500);
        }
    }

    public function translations(Article $article)
    {
        return ApiResponse::success([
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
                    'slug' => $article->slug_id,
                    'description' => $article->description_id,
                    'content' => $article->content_id,
                ],
                'en' => [
                    'title' => $article->title_en,
                    'slug' => $article->slug_en,
                    'description' => $article->description_en,
                    'content' => $article->content_en,
                ],
            ],
            'categories' => $article->categories,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
        ], __('messages.articles.translations_success'));
    }

    private function uploadThumbnail($file, $title): string
    {
        $filename = time() . '_' . Str::slug($title) . '.webp';
        $path = 'articles/' . $filename;

        $manager = new ImageManager(new Driver());

        $image = $manager
            ->read($file)
            ->resize(1200, null)
            ->toWebp(85);

        Storage::disk('public')->put($path, (string) $image);

        OptimizerChainFactory::create()
            ->optimize(storage_path('app/public/' . $path));

        return Storage::url($path);
    }

    private function deleteThumbnail($url): void
    {
        $path = str_replace(asset('storage/'), '', $url);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
