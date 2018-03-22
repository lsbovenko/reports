<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Project;

class RemoveOldProjects2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $projects = [
            'Azap',
            'AIMLON CPA P.C.',
            'Suitable',
            'Shoutoutpost',
            'Bjornberry',
            'SocialJukebox',
            'babysitter',
            'HOL',
            'Sunny Pahal',
            'Aimlon',
            'oxbridgecoins',
            'dynamofitness',
            'Aimlon CPA',
            'vizibal',
            'сервис курсов валют',
            'Tbalza',
            'node.js',
            'Shoutout post',
            'design website',
            'Socialyz.it',
            'AquaTru',
            'Blockchain Coin',
            'Gamaxa',
        ];
        foreach ($projects as $projectName) {
            $projectName = trim($projectName);
            /** @var \App\Models\Project $projectModel */
            $projectModel = Project::where('name', '=', $projectName)->first();
            if ($projectModel) {
                if ($projectModel->is_active) {
                    throw new \Exception("The project $projectName is active.");
                }
                /** @var \App\Models\Project $childProject */
                foreach ($projectModel->children as $childProject) {
                    $childProjectName = $childProject->getFullName();
                    if ($childProject->is_active) {
                        throw new \Exception("The child project $childProjectName is active.");
                    }
                    /** @var \App\Models\Report $report */
                    foreach ($childProject->reports as $report) {
                        $report->project_id = null;
                        $report->is_tracked = 0;
                        $report->task = $childProjectName;
                        $report->save();
                    }
                    $childProject->delete();
                }
                /** @var \App\Models\Report $report */
                foreach ($projectModel->reports as $report) {
                    $report->project_id = null;
                    $report->is_tracked = 0;
                    $report->task = $projectName;
                    $report->save();
                }
                $projectModel->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
