<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Service\ProjectManagerStatistics;
use App\Helpers\Carbon as CarbonHelper;

/**
 * Class ProjectManager
 * @package App\Http\Controllers
 */
class ProjectManager extends Controller
{
    /**
     * @param ProjectManagerStatistics $service
     * @return \Illuminate\View\View
     */
    public function index(ProjectManagerStatistics $service)
    {
        $date = Carbon::today();
        if (CarbonHelper::isDayOff($date)) {
            $date = Carbon::parse('last friday');
        }

        $projects = Project::whereNull('parent_id')
            ->orderBy('name', 'ASC')
            ->get();

        $firstReport = Report::orderBy('date', 'asc')->first();
        return view(
            'pm.index',
            [
                'projects' => $projects,
                'js' => [
                    'selectedDate' => $date->toIso8601String(),
                    'minDate' => $firstReport ? Carbon::parse($firstReport->date)->toIso8601String() : '',
                    'pmStatistics' => $service->getProjectManagerStatistics($date, null, null),
                    'startDate' => $date->toFormattedDateString(),
                ]
            ]
        );
    }

    /**
     * @param Request $request
     * @param ProjectManagerStatistics $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request, ProjectManagerStatistics $service)
    {
        $project = Project::find($request->get('project_id'));
        list($date, $endDate) = $this->retrieveDates($request);

        return response()->json([
            'pmStatistics' => $service->getProjectManagerStatistics($date, $endDate, $project),
            'startDate' => $date->toFormattedDateString(),
            'endDate' => isset($endDate) ? $endDate->toFormattedDateString() : '',
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function retrieveDates(Request $request)
    {
        $dates = $request->get('dates');
        return [Carbon::parse($dates[0]), isset($dates[1]) ? Carbon::parse($dates[1])->endOfDay() : null];
    }
}