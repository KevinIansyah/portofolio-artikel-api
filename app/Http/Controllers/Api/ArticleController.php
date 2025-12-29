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

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::where('status', 'published')->with(['categories:id,name']);

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // if ($request->has('tag')) {
        //     $query->whereHas('tags', function ($q) use ($request) {
        //         $q->where('slug', $request->tag);
        //     });
        // }


        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $articles = $query->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return ApiResponse::paginated($articles, 'Daftar artikel berhasil diambil');
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->with(['categories:id,name,slug', 'user:id,name,email,avatar'])
            ->firstOrFail();

        return ApiResponse::success($article, 'Detail artikel berhasil diambil');
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
                    $request->title
                );
            }

            $article = Article::create($data);

            if ($request->has('categories')) {
                $article->categories()->sync($request->categories);
            }

            // if ($request->has('tags')) {
            //     $article->tags()->sync($request->tags);
            // }

            DB::commit();

            $article->load(['categories:id,name', 'user:id,name']);

            return ApiResponse::success($article, 'Artikel berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to store article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'title' => $request->title,
            ]);

            return ApiResponse::error('Gagal menyimpan artikel', 500);
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
                    $request->title
                );
            }

            $article->update($data);

            if ($request->has('categories')) {
                $article->categories()->sync($request->categories);
            }

            // if ($request->has('tags')) {
            //     $article->tags()->sync($request->tags);
            // }

            DB::commit();

            $article->load(['categories:id,name', 'user:id,name']);

            return ApiResponse::success($article, 'Artikel berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ]);

            return ApiResponse::error('Gagal memperbarui artikel', 500);
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
            // $article->tags()->detach();

            $article->delete();

            DB::commit();

            return ApiResponse::success(null, 'Artikel berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete article: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
            ]);

            return ApiResponse::error('Gagal menghapus artikel', 500);
        }
    }

    private function uploadThumbnail($file, $title): string
    {
        $filename = time() . '_' . Str::slug($title) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('articles', $filename, 'public');

        return asset('storage/' . $path);
    }

    private function deleteThumbnail($url): void
    {
        $path = str_replace(asset('storage/'), '', $url);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
