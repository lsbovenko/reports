<?php

namespace App\Http\Controllers;

use App\Models\Auth\User;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Statistics extends Controller
{
    public function index(\App\Service\Statistics $service)
    {
        $date = Carbon::today();
        if ($date->isSaturday() || $date->isSunday()) {
            $date = Carbon::parse('last friday');
        }

        return view(
            'statistics.index',
            [
                'js' => [
                    'users' => User::select(['id', 'name', 'last_name', 'is_active'])
                        ->where('is_report_required', 1)
                        ->where('is_active', 1)
                        ->orderBy('last_name')
                        ->get(),

                    'statistics' => $service->getReportsSummary(null, $date),
                    'selectedDate' => $date->toIso8601String(),
                    'minDate' => Carbon::parse(Report::orderBy('id', 'asc')->first()->date)->toIso8601String(),
                ]
            ]
        );
    }

    public function filter(Request $request, \App\Service\Statistics $service)
    {
        $user = $this->findUser($request);
        list($date, $endDate) = $this->retrieveDates($request);

        return response()
            ->json($service->getReportsSummary($user, $date, $endDate));
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

    private function retrieveDates(Request $request)
    {
        $dates = $request->get('dates');
        return [Carbon::parse($dates[0]), isset($dates[1]) ? Carbon::parse($dates[1]) : null];
    }

    private function findUser(Request $request)
    {
        return $user = User::find($request->get('user_id'));
    }
}
