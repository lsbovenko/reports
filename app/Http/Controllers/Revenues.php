<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Service\RevenueStatistics;
use App\Helpers\Carbon as CarbonHelper;

/**
 * Class Revenues
 * @package App\Http\Controllers
 */
class Revenues extends Controller
{
    /**
     * @param RevenueStatistics $service
     * @return \Illuminate\View\View
     */
    public function index(RevenueStatistics $service)
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
            'revenues.index',
            [
                'projects' => $projects,
                'js' => [
                    'fixedPriceRevenue' => $service->getRevenue($date, null, null, true),
                    'notFixedPriceRevenue' => $service->getRevenue($date),
                    'selectedDate' => $date->toIso8601String(),
                    'minDate' => $firstReport ? Carbon::parse($firstReport->date)->toIso8601String() : '',
                ]
            ]
        );
    }

    /**
     * @param Request $request
     * @param RevenueStatistics $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request, RevenueStatistics $service)
    {
        $project = Project::find($request->get('project_id'));
        list($date, $endDate) = $this->retrieveDates($request);

        return response()->json([
            'fixedPriceRevenue' => $service->getRevenue($date, $endDate, $project, true),
            'notFixedPriceRevenue' => $service->getRevenue($date, $endDate, $project),
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
