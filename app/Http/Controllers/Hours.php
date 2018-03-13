<?php

namespace App\Http\Controllers;

use App\Models\Auth\User;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Service\UserStatistics;
use App\Helpers\Carbon as CarbonHelper;

/**
 * Class Hours
 * @package App\Http\Controllers
 */
class Hours extends Controller
{
    /**
     * @param UserStatistics $service
     * @return \Illuminate\View\View
     */
    public function index(UserStatistics $service)
    {
        $date = Carbon::today();
        if (CarbonHelper::isDayOff($date)) {
            $date = Carbon::parse('last friday');
        }

        $firstReport = Report::orderBy('date', 'asc')->first();
        return view(
            'hours.index',
            [
                'js' => [
                    'users' => User::select(['id', 'name', 'last_name', 'is_active'])
                        ->where('is_revenue_required', 1)
                        ->where('is_active', 1)
                        ->orderBy('last_name')
                        ->orderBy('name')
                        ->get(),
                    'selectedDate' => $date->toIso8601String(),
                    'minDate' => $firstReport ? Carbon::parse($firstReport->date)->toIso8601String() : '',
                    'usersAndLoggedMinutes' => $service->getUsersAndLoggedMinutes()
                ]
            ]
        );
    }

    /**
     * @param Request $request
     * @param UserStatistics $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request, UserStatistics $service)
    {
        $user = User::find($request->get('user_id'));
        list($date, $endDate) = $this->retrieveDates($request);

        return response()->json([
            'quantityWorkedDays' => $endDate ? $endDate->diffInWeekdays($date) : (int)!CarbonHelper::isDayOff($date),
            'usersAndLoggedMinutes' => $service->getUsersAndLoggedMinutes($user, $date, $endDate)
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
