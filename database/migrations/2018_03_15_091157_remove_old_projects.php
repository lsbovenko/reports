<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Project;

class RemoveOldProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $projects = [
            'laravel',
            'php/laravel',
            'суппорт',
            'vagrant',
            'php',
            'js',
            'тех адаптация нового сотрудника',
            'development process',
            'avantcore',
            'blackcoin',
            'vortex',
            'VR Adults',
            'trenddemon',
            'RingR',
            'Больница',
            'Gianovi',
            'Go Iot',
            'Dmt',
            'Tipsandfood',
            'n/a',
            'Hard Fork',
            'marketplacephp.com',
            'Re-mil.com',
            'Shoutpost',
            'Remil.com',
            'dynamaze',
            'Simpapply',
            'Etailerlab',
            'Hardtoprovide',
            'Ikantam',
            'StoryThat',
            'Sectre',
            'Develop HTML Template for SharePoint',
            'dmt_planner',
            'nxmoov',
            'Cargomatic',
        ];
        foreach ($projects as $projectName) {
            $projectName = trim($projectName);
            $projectModel = Project::where('name', '=', $projectName)->first();
            if ($projectModel) {
                if ($projectModel->is_active) {
                    throw new \Exception("The project $projectName is active.");
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
