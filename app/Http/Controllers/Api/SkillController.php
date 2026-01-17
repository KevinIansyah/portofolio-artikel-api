<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Skill\StoreRequest;
use App\Http\Requests\Skill\UpdateRequest;
use App\Models\Skill;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillController extends Controller
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function index()
    {
        $skills = Skill::select('id', 'name', 'slug', 'dark_icon_url', 'light_icon_url')
            ->orderBy('name')
            ->get();

        return ApiResponse::success($skills, __('messages.skills.list_success'));
    }

    public function indexPaginated(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 30, 40, 50]) ? $perPage : 20;

        $search = $request->get('search');

        $query = Skill::select('id', 'name', 'slug', 'dark_icon_url', 'light_icon_url');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search . '%');
            });
        }

        $skills = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', $perPage));

        return ApiResponse::paginated($skills, __('messages.skills.list_success'));
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('dark_icon') && $request->hasFile('light_icon')) {
                $data['dark_icon_url'] = $this->imageService->upload(
                    file: $request->file('dark_icon'),
                    name: $data['name'],
                    folder: 'icons/dark'
                );

                $data['light_icon_url'] = $this->imageService->upload(
                    file: $request->file('light_icon'),
                    name: $data['name'],
                    folder: 'icons/light'
                );
            }

            $skill = Skill::create($data);

            return ApiResponse::success($skill, __('messages.skills.store_success'), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create skill: ' . $e->getMessage(), [
                'name' => $request->name,
            ]);

            return ApiResponse::error(__('messages.skills.store_failed'), 500);
        }
    }

    public function edit(Skill $skill)
    {
        $skill = [
            'id' => $skill->id,
            'name' => $skill->name,
            'dark_icon_url' => $skill->dark_icon_url,
            'light_icon_url' => $skill->light_icon_url,
            'created_at' => $skill->created_at,
            'updated_at' => $skill->updated_at,
        ];

        return ApiResponse::success($skill, __('messages.skills.translations_success'));
    }

    public function update(UpdateRequest $request, Skill $skill)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('dark_icon')) {
                $data['dark_icon_url'] = $this->imageService->update(
                    oldUrl: $skill->dark_icon_url,
                    newFile: $request->file('dark_icon'),
                    name: $data['title'],
                    folder: 'icons/dark'
                );
            }

            if ($request->hasFile('light_icon')) {
                $data['light_icon_url'] = $this->imageService->update(
                    oldUrl: $skill->light_icon_url,
                    newFile: $request->file('light_icon'),
                    name: $data['title'],
                    folder: 'icons/light'
                );
            }

            $skill->update($data);

            return ApiResponse::success($skill, __('messages.skills.update_success'));
        } catch (\Exception $e) {
            Log::error('Failed to update skill: ' . $e->getMessage(), [
                'id' => $skill->id,
                'name' => $request->name,
            ]);

            return ApiResponse::error(__('messages.skills.update_failed'), 500);
        }
    }

    public function destroy(Skill $skill)
    {
        try {
            $projectsCount = $skill->projects()->count();

            if ($projectsCount > 0) {
                return ApiResponse::error(
                    __('messages.general.relation_constraint'),
                    400
                );
            }

            if ($skill->dark_icon_url) {
                $this->imageService->delete($skill->dark_icon_url);
            }

            if ($skill->light_icon_url) {
                $this->imageService->delete($skill->light_icon_url);
            }

            $skill->delete();

            return ApiResponse::success(null, __('messages.skills.delete_success'));
        } catch (\Exception $e) {
            Log::error('Failed to delete skill: ' . $e->getMessage(), [
                'id' => $skill->id,
                'name' => $skill->name,
            ]);

            return ApiResponse::error(__('messages.skills.delete_failed'), 500);
        }
    }
}
