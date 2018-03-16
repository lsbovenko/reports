<?php

namespace App\Service;

use App\Models\Project;
use Illuminate\Http\Request;

/**
 * Class ProjectUpdater
 * @package App\Service
 */
class ProjectUpdater
{
    /**
     * @param Project $project
     * @param Request $request
     */
    public function handleSaveRequest(Project $project, Request $request)
    {
        $checkReportsForParentProject = true;
        $project->name = $request->get('name');
        $project->is_active = (bool)$request->get('is_active');
        $project->is_fixed_price = (bool)$request->get('is_fixed_price');
        $project->save();
        if (!empty($request->get('child'))) {
            foreach ($request->get('child') as $childData) {
                if ($childProjectId = (int)$childData['id']) {
                    $childProject = Project::find($childProjectId);
                } else {
                    $childProject = new Project();
                }
                $childProject->rate = $childData['rate'];
                $childProject->name = $childData['name'];
                $childProject->is_active = (bool)$childData['is_active'];
                $childProject->parent_id = $project->id;
                $childProject->is_fixed_price = $project->is_fixed_price;
                $childProject->save();

                if ($checkReportsForParentProject && $project->reports()->count()) {
                    $this->moveParentReportsToChild($project, $childProject);
                    $checkReportsForParentProject = false;
                }
            }
            $project->rate = 0;
        } else {
            $project->rate = $request->get('rate');
        }
        $project->save();
    }

    /**
     * @param Project $parentProject
     * @param Project $childProject
     */
    private function moveParentReportsToChild(Project $parentProject, Project $childProject)
    {
        /** @var \App\Models\Report $report */
        foreach ($parentProject->reports as $report) {
            $report->project_id = $childProject->id;
            $report->save();
        }
    }
}
