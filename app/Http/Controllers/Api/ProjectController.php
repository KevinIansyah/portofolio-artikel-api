<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreRequest;
use App\Http\Requests\Projects\UpdateRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $query = Project::where('status', 'published')
            ->byLocale($locale)
            ->with(['categories:id,name']);

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

        // if ($request->has('search')) {
        //     $search = $request->search;
        //     $query->where(function ($q) use ($search) {
        //         $q->where('title', 'like', "%{$search}%")
        //             ->orWhere('description', 'like', "%{$search}%");
        //     });
        // }

        $projects = $query->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return ApiResponse::paginated($projects, __('messages.projects.list_success'));
    }

    public function show($slug)
    {
        $locale = app()->getLocale();

        $project = Project::where("slug_{$locale}", $slug)
            ->where('status', 'published')
            ->with(['categories:id,name,slug', 'user:id,name,email,avatar'])
            ->firstOrFail();

        return ApiResponse::success($project, 'Detail proyek berhasil diambil');
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

            if ($data['status'] === 'published') {
                $data['published_at'] = now();
            }

            $project = Project::create($data);

            if ($request->has('categories')) {
                $project->categories()->sync($request->categories);
            }

            // if ($request->has('tags')) {
            //     $project->tags()->sync($request->tags);
            // }

            DB::commit();

            $project->load(['categories:id,name', 'user:id,name']);

            return ApiResponse::success($project, __('messages.projects.store_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'title' => $request->title,
            ]);

            return ApiResponse::error(__('messages.projects.store_failed'), 500);
        }
    }

    public function update(UpdateRequest $request, Project $project)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($request->hasFile('thumbnail')) {
                if ($project->thumbnail_url) {
                    $this->deleteThumbnail($project->thumbnail_url);
                }

                $data['thumbnail_url'] = $this->uploadThumbnail(
                    $request->file('thumbnail'),
                    $request->title
                );
            }

            if ($data['status'] === 'published' && $project->published_at === null) {
                $data['published_at'] = now();
            }

            $project->update($data);

            if ($request->has('categories')) {
                $project->categories()->sync($request->categories);
            }

            // if ($request->has('tags')) {
            //     $project->tags()->sync($request->tags);
            // }

            DB::commit();

            $project->load(['categories:name', 'user:name']);

            return ApiResponse::success($project, __('messages.projects.update_success'));
        } catch (\Exception $e) {
            Log::error('Failed to update project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]);

            return ApiResponse::error(__('messages.projects.update_failed'), 500);
        }
    }


    public function destroy(Request $request, Project $project)
    {
        try {
            DB::beginTransaction();

            if ($project->thumbnail_url) {
                $this->deleteThumbnail($project->thumbnail_url);
            }

            $project->categories()->detach();
            // $project->tags()->detach();

            $project->delete();

            DB::commit();

            return ApiResponse::success(null, __('messages.projects.delete_success'));
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]);

            return ApiResponse::error(__('messages.projects.delete_failed'), 500);
        }
    }

    public function translations(Project $project)
    {
        return ApiResponse::success([
            'id' => $project->id,
            'user_id' => $project->user_id,
            'thumbnail_url' => $project->thumbnail_url,
            'status' => $project->status,
            'published_at' => $project->published_at,
            'translations' => [
                'id' => [
                    'title' => $project->title_id,
                    'slug' => $project->slug_id,
                    'description' => $project->description_id,
                    'content' => $project->content_id,
                ],
                'en' => [
                    'title' => $project->title_en,
                    'slug' => $project->slug_en,
                    'description' => $project->description_en,
                    'content' => $project->content_en,
                ],
            ],
            'categories' => $project->categories,
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
        ], __('messages.projects.translations_success'));
    }

    private function uploadThumbnail($file, $title): string
    {
        $filename = time() . '_' . Str::slug($title) . '.webp';
        $path = 'projects/' . $filename;

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
