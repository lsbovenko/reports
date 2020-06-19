<?php

namespace App\Http\Controllers;

use App\Models\Auth\User;
use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Project as ProjectTransformer;

class Statistics extends Controller
{
    public function index(\App\Service\Statistics $service, ProjectTransformer $projectTransformer)
    {
        $date = Carbon::today();
        if ($date->isSaturday() || $date->isSunday()) {
            $date = Carbon::parse('last friday');
        }

        $firstReport = Report::orderBy('date', 'asc')->first();

        $activeProjects = Project::select()
            ->where('is_active', '=', 1)
            ->get();
        $activeProjects = $projectTransformer->transformCollection($activeProjects);

        return view(
            'statistics.index',
            [
                'activeProjects' => $activeProjects,
                'js' => [
                    'users' => User::select(['id', 'name', 'last_name', 'is_active'])
                        ->where('is_report_required', 1)
                        ->where('is_active', 1)
                        ->orderBy('last_name')
                        ->orderBy('name')
                        ->get(),

                    'statistics' => $service->getReportsSummary(null, $date),
                    'selectedDate' => $date->toIso8601String(),
                    'minDate' => $firstReport ? Carbon::parse($firstReport->date)->toIso8601String() : '',
                ]
            ]
        );
    }

    public function filter(Request $request, \App\Service\Statistics $service)
    {
        $user = $this->findUser($request);
        list($date, $endDate) = $this->retrieveDates($request);
        $isMeeting = $request->get('is_meeting');

        return response()
            ->json($service->getReportsSummary($user, $date, $endDate, $isMeeting));
    }

    public function chartData(Request $request, \App\Service\Statistics $service)
    {
        $user = $this->findUser($request);
        list($startDate, $endDate) = $this->retrieveDates($request);

        if (null === $user) {
            return response()
                ->json(['error' => 'insufficient data: user id is required']);
        }

        if (null === $endDate) {
            $startDate = Carbon::parse('first day of this month');
            $endDate = Carbon::parse('last day of this month');
        }

        return response()
            ->json($service->getStackedDatasets($user, $startDate, $endDate));
    }

    public function loggedTime (Request $request, \App\Service\Statistics $service)
    {
        $date = Carbon::parse($request->get('date'));
        $reportsSummary = $service->getReportsSummary($request->user(), $date);

        return response()->json([
            'statistics' => $reportsSummary ? current(current($reportsSummary)) : null,
        ], 200);
    }

    private function retrieveDates(Request $request)
    {
        $dates = $request->get('dates');
        return [Carbon::parse($dates[0]), isset($dates[1]) ? Carbon::parse($dates[1]) : null];
    }

    private function findUser(Request $request)
    {
        return $user = User::find($request->get('user_id'));
    }

    public function timeAllPeriod(Request $request)
    {
        $projectId = Project::where('name', $request->get('project'))->pluck('id')->first();
        $reports = Report::where('user_id', $request->get('user_id'))
            ->where('project_id', $projectId);
        if ($isMeeting = $request->get('is_meeting')) {
            $reports = $reports->where('is_meeting', $isMeeting);
        }
        $workedMinutes = $reports->sum('worked_minutes');

        return response()->json(['workedMinutes' => $workedMinutes]);
    }
}
