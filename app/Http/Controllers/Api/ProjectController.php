<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreRequest;
use App\Http\Requests\Projects\UpdateRequest;
use App\Models\Project;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $categorySlug = 'slug_' . $locale;

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 30, 40, 50]) ? $perPage : 20;

        $search = $request->get('search');

        $query = Project::byLocale($locale)
            ->with(['categories:id,name_id,name_en,slug_id,slug_en', 'skills:id,name,slug', 'user:id,name']);

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($categorySlug, $request) {
                $q->where($categorySlug, $request->category);
            });
        }

        if ($request->has('skill')) {
            $query->whereHas('skills', function ($q) use ($request) {
                $q->where('slug', $request->skill);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title_id', 'like', $search . '%')
                    ->orWhere('title_en', 'like', $search . '%');
            });
        }

        $projects = $query->latest('published_at')
            ->paginate($request->get('per_page', $perPage));

        return ApiResponse::paginated($projects, __('messages.projects.list_success'));
    }

    public function show($slug)
    {
        $locale = app()->getLocale();

        $project = Project::where("slug_{$locale}", $slug)
            ->where('status', 'published')
            ->with(['categories:id,name_id,name_en,slug_id,slug_en', 'skills:id,name,slug', 'user:id,name,email,avatar_url'])
            ->firstOrFail();

        return ApiResponse::success($project, __('messages.projects.detail_success'));
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
                    folder: 'projects'
                );
            }

            if ($data['status'] === 'published') {
                $data['published_at'] = now();
            }

            $project = Project::create($data);

            if ($request->has('categories')) {
                $project->categories()->sync($request->categories);
            }

            if ($request->has('skills')) {
                $project->skills()->sync($request->skills);
            }

            DB::commit();

            $project->load(['categories:id,name_id,name_en,slug_id,slug_en', 'skills:id,name,slug', 'user:id,name']);

            return ApiResponse::created($project, __('messages.projects.store_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'title_id' => $request->title_id,
            ]);

            return ApiResponse::serverError(__('messages.projects.store_failed'), 500);
        }
    }

    public function edit(Project $project)
    {
        $project = [
            'id' => $project->id,
            'user_id' => $project->user_id,
            'thumbnail_url' => $project->thumbnail_url,
            'demo_url' => $project->demo_url,
            'project_url' => $project->project_url,
            'status' => $project->status,
            'published_at' => $project->published_at,
            'translations' => [
                'id' => [
                    'title' => $project->title_id,
                    'description' => $project->description_id,
                    'content' => $project->content_id,
                ],
                'en' => [
                    'title' => $project->title_en,
                    'description' => $project->description_en,
                    'content' => $project->content_en,
                ],
            ],
            'categories' => $project->categories,
            'skills' => $project->skills,
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
        ];

        return ApiResponse::success($project, __('messages.projects.translations_success'));
    }

    public function update(UpdateRequest $request, Project $project)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail_url'] = $this->imageService->update(
                    oldUrl: $project->thumbnail_url,
                    newFile: $request->file('thumbnail'),
                    name: $data['title_id'],
                    folder: 'projects'
                );
            }

            if ($data['status'] === 'published' && $project->published_at === null) {
                $data['published_at'] = now();
            }

            $project->update($data);

            if ($request->has('categories')) {
                $project->categories()->sync($request->categories);
            }

            if ($request->has('skills')) {
                $project->skills()->sync($request->skills);
            }

            DB::commit();

            $project->load(['categories:id,name_id,name_en,slug_id,slug_en', 'skills:id,name,slug', 'user:id,name']);

            return ApiResponse::updated($project, __('messages.projects.update_success'));
        } catch (\Exception $e) {
            Log::error('Failed to update project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]);

            return ApiResponse::serverError(__('messages.projects.update_failed'), 500);
        }
    }


    public function destroy(Request $request, Project $project)
    {
        try {
            DB::beginTransaction();

            if ($project->thumbnail_url) {
                $this->imageService->delete($project->thumbnail_url);
            }

            $project->categories()->detach();
            $project->skills()->detach();

            $project->delete();

            DB::commit();

            return ApiResponse::deleted(__('messages.projects.delete_success'));
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'project_id' => $project->id,
            ]);

            return ApiResponse::serverError(__('messages.projects.delete_failed'), 500);
        }
    }
}
