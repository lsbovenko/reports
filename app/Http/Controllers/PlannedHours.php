<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Repositories\PlannedHours as PlannedHoursRepository;
use Illuminate\Http\Request;
use App\Http\Requests\PlannedHoursRequest;
use App\Models\PlannedHours as PlannedHoursModel;

class PlannedHours extends Controller
{
    protected $repository;

    public function __construct(PlannedHoursRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Carbon $carbon)
    {
        $years = $this->repository->getYears()->pluck('year')->toArray();
        $years = array_unique(array_merge([$carbon->year + 1, $carbon->year], $years));

        return view('planned_hours.index', compact('years'));
    }

    public function edit(Request $request)
    {
        $year = $request->route('year');
        $plannedHours = $this->repository->getPlannedHoursByYear($year);

        return view('planned_hours.edit', [
            'year' => $year,
            'plannedHours' => $plannedHours
        ]);
    }

    public function update(PlannedHoursRequest $request)
    {
        $year = $request->route('year');
        $plannedHours = $request->get('planned_hours');

        $this->updatePlannedHours($year, $plannedHours);
        $request->session()->flash('alert-success', trans('reports.changes_planned_hours_success', ['year' => $year]));

        return redirect()->route('planned-hours.index');
    }

    protected function updatePlannedHours(int $year, array $plannedHours)
    {
        PlannedHoursModel::where('year', $year)->delete();
        foreach ($plannedHours as $key => $plannedHour) {
            $plannedHoursArray[] = [
                'year' => $year,
                'month' => $key + 1,
                'planned_hours' => $plannedHour
            ];
        }
        PlannedHoursModel::insert($plannedHoursArray);
    }
}
