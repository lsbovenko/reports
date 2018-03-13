<?php

namespace App\Transformers;

use App\Models\Project as ProjectModel;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Project
 * @package App\Transformers
 */
class Project
{
    /**
     * @param \Traversable $projects
     * @return array
     */
    public function transformCollection($projects)
    {
        $results = [];
        /** @var \App\Models\Project $project */
        foreach ($projects as $project) {
            $results[] = $this->transform($project);
        }

        return $results;
    }

    /**
     * @param ProjectModel $project
     * @return array
     */
    public function transform(ProjectModel $project): array
    {
        $result = $project->toArray();
        $name = $project->name;
        if ($project->parent_id) {
            $name = $project->getFullName();
            $result['parentName'] = $project->parent->name;
        }
        $result['fullName'] = $result['text'] = $name;

        return $result;
    }

    /**
     * @param \Traversable $projects
     * @return array
     */
    public function removeParentIfExistsChildren($projects)
    {
        $resultProjects = [];
        foreach ($projects as $project) {
            if (!$project->parent_id && $project->children()->count()) {
                /** @var \App\Models\Project $child */
                foreach ($project->children as $child) {
                    if ($child->is_active) {
                        $resultProjects[] = $child;
                    }
                }
            } else {
                $resultProjects[] = $project;
            }
        }
        return $resultProjects;
    }
}
