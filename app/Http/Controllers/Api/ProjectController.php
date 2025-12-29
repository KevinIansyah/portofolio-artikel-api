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

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::where('status', 'published')
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

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest('published_at')
            ->paginate($request->get('per_page', 12));

        return ApiResponse::paginated($projects, 'Daftar proyek berhasil diambil');
    }

    public function show($slug)
    {
        $project = Project::where('slug', $slug)
            ->where('status', 'published')
            ->with(['categories:id,name,slug', 'user:id,name,email,avatar'])
            ->firstOrFail();

        // if (!$project) {
        //     return ApiResponse::error('Proyek tidak ditemukan', 404);
        // }

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

            $project = Project::create($data);

            if ($request->has('categories')) {
                $project->categories()->sync($request->categories);
            }

            // if ($request->has('tags')) {
            //     $project->tags()->sync($request->tags);
            // }

            DB::commit();

            $project->load(['categories:id,name', 'user:id,name']);

            return ApiResponse::success($project, 'Proyek berhasil dibuat', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'title' => $request->title,
            ]);

            return ApiResponse::error('Gagal membuat proyek. Silakan coba lagi.', 500);
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

            $project->update($data);

            if ($request->has('categories')) {
                $project->categories()->sync($request->categories);
            }

            // if ($request->has('tags')) {
            //     $project->tags()->sync($request->tags);
            // }

            DB::commit();

            $project->load(['categories:name', 'user:name']);

            return ApiResponse::success($project, 'Proyek berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]);

            return ApiResponse::error('Gagal memperbarui proyek', 500);
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

            return ApiResponse::success(null, 'Proyek berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]);

            return ApiResponse::error('Gagal menghapus proyek', 500);
        }
    }

    private function uploadThumbnail($file, $title): string
    {
        $filename = time() . '_' . Str::slug($title) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('projects', $filename, 'public');

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
