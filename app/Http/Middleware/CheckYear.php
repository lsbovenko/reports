<?php

namespace App\Http\Middleware;

use App\Repositories\PlannedHours as PlannedHoursRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Closure;

class CheckYear
{
    protected $repository;
    protected $carbon;

    public function __construct(PlannedHoursRepository $repository, Carbon $carbon)
    {
        $this->repository = $repository;
        $this->carbon = $carbon;
    }

    public function handle(Request $request, Closure $next)
    {
        $year = $request->route('year');
        $countPlannedHours = $this->repository->getPlannedHoursByYear($year)->count();
        $currentYear = $this->carbon->year;
        if (!($countPlannedHours || $year >= $currentYear)) {
            abort(404);
        }

        return $next($request);
    }
}
