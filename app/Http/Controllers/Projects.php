<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Service\ProjectUpdater;
use App\Transformers\Project as ProjectTransformer;

/**
 * Class Projects
 * @package App\Http\Controllers
 */
class Projects extends Controller
{
    /**
     * @param Request $request
     * @param ProjectTransformer $projectTransformer
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, ProjectTransformer $projectTransformer)
    {
        $searchQuery = $request->get('q');
        if ($searchQuery) {
            $projects = Project::where('name', 'like', '%' . $searchQuery . '%')
                ->where('is_active', '=' ,1)
                ->whereNull('parent_id')
                ->with('children')
                ->take(10)
                ->get();
            $resultProjects = $projectTransformer->removeParentIfExistsChildren($projects);
        } else {
            $resultProjects = Project::allActiveRelatedToUser(\Auth::user())->get();
        }

        return response()->json(['items' => $projectTransformer->transformCollection($resultProjects)]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('projects.index', [
            'projects' => $this->getQuery($request)->paginate(15)
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('projects.edit', [
            'title' => 'Создать проект',
            'js' => [
                'project' => null,
                'submitUrl' => route('projects.save'),
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param ProjectUpdater $projectUpdater
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request, ProjectUpdater $projectUpdater)
    {
        $project = new Project();
        $this->handleSaveRequest($request, $project, $projectUpdater);
        return response()->json(['success' => true]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        /** @var \App\Models\Project $user */
        $project = Project::findOrFail($request->route('id'));
        if ($project->parent_id) {
            return response()->view('404', [], 404);
        }

        $project->children;//it loads children to the Model
        return view('projects.edit', [
            'title' => 'Редактировать проект',
            'js' => [
                'project' => $project,
                'submitUrl' => route('projects.edit', ['id' => $project->id]),
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param ProjectUpdater $projectUpdater
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, ProjectUpdater $projectUpdater)
    {
        /** @var \App\Models\Project $user */
        $project = Project::findOrFail($request->route('id'));
        if ($project->parent_id) {
            return response(null, 422);
        }
        $this->handleSaveRequest($request, $project, $projectUpdater);
        return response()->json(['success' => true]);
    }

    /**
     * @param Request $request
     * @param Project $project
     * @param ProjectUpdater $projectUpdater
     */
    private function handleSaveRequest(Request $request, Project $project, ProjectUpdater $projectUpdater)
    {
        $this->validateRequest($project, $request);
        $projectUpdater->handleSaveRequest($project, $request);
    }

    /**
     * @param Request $request
     * @param Project $project
     * @return void|\Symfony\Component\HttpFoundation\Response
     */
    private function validateRequest(Project $project, Request $request)
    {
        $rules = $this->getValidationRules($request, $project);
        $this->validate($request, $rules);

        if (!empty($request->get('child'))) {
            foreach ($request->get('child') as $index => $childData) {
                if ($childProjectId = (int)$childData['id']) {
                    $childProject = Project::find($childProjectId);
                    if (!$childProject || $childProject->parent_id != $project->id) {
                        return response(null, 422);
                    }
                }
                $childRules = $this->getChildValidationRules($index);
                $this->validate($request, $childRules);
            }
        }
    }

    /**
     * @param Request $request
     * @param Project|null $project
     * @return array
     */
    private function getValidationRules(Request $request, Project $project = null)
    {
        $rules = [
            'name' => 'required|max:255',
        ];
        if (!$project || $request->get('name') != $project->name) {
            $rules['name'] .='|unique:projects';
        }
        if (empty($request->get('child'))) {
            $rules['rate'] = 'required|integer';
        }
        return $rules;
    }

    /**
     * @param $index
     * @return array
     */
    private function getChildValidationRules($index)
    {
        $rules = [
            'child.' . $index . '.name' => 'required|max:255',
            'child.' . $index . '.rate' => 'required|integer|max:1000',
        ];
        return $rules;
    }


    /**
     * handle request
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    private function getQuery(Request $request)
    {
        $query = Project::query()
            ->whereNull('parent_id')
            ->orderBy('id', 'DESC');

        if ($name = $request->get('name')) {
            $name = '%' .$name . '%';

            $query->where('name', 'LIKE',  $name);
        }

        return $query;
    }
}
