<?php


namespace App\Service;

use App\Models\Auth\User;
use App\Models\Project;
use App\Models\ProjectInSkills;
use App\Models\Report;
use GuzzleHttp\Client;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;

/**
 * Class Skills
 * @package App\Service
 */
class Skills
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Encrypter
     */
    protected $encrypter;

    /**
     * Skills constructor.
     * @param Client $client
     * @param Encrypter $encrypter
     */
    public function __construct(Client $client, Encrypter $encrypter)
    {
        $this->client = $client;
        $this->encrypter = $encrypter;
    }

    /**
     * @param Report $report
     * @return bool
     */
    public function addProjectToSkillsService(Report $report)
    {
        try {
            $project = $report->project;
            $targetProject = $project->parent ? $project->parent : $project;
            if ($this->isProjectAlreadyAdded($report->user, $targetProject)) {
                return false;
            }

            $this->createProjectInSkillsService($targetProject, $report);
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }

    /**
     * @param Project $project
     * @param Report $report
     */
    private function createProjectInSkillsService(Project $project, Report $report)
    {
        $model = new ProjectInSkills;
        $model->user_id = $report->user->id;
        $model->project_id = $project->id;
        $model->save();
        $this->createInRemoteService($project, $report);

    }

    /**
     * @param Project $project
     * @param Report $report
     * @return $this
     */
    private function createInRemoteService(Project $project, Report $report)
    {
        $data = $this->encrypter->encrypt([
            'project_name' => $project->name,
            'user' => [
                'email' => $report->user->email,
                'name' => $report->user->name,
                'last_name' => $report->user->last_name,
            ]
        ]);
        $url = config('app.skills_url') . config('app.skills_receive_project_path_receiver');

        $this->client->post($url, ['body' => $data]);

        return $this;
    }

    /**
     * @param User $user
     * @param Project $project
     * @return bool
     */
    private function isProjectAlreadyAdded(User $user, Project $project)
    {
        $projectInSkills = ProjectInSkills::findOneByUserByProject($user, $project);

        return $projectInSkills ? true : false;
    }
}
