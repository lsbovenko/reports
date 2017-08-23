<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Reports extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view(
            'reports.create',
            [
                'projects' => Project::allRelatedToUser(\Auth::user())->get(),
                'js' => [
                    'latestProjects' => Project::select('name')
                        ->whereIn('id', Report::findLatestTracked(Auth::user())->select('project_id')->get())
                        ->get(),
                ]
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $date = Carbon::parse($request->input('date'));

        if ($date->greaterThan(Carbon::today())) {
            return response()->json(['error' => 'Date can\'t be greater than today'], 400);
        }

        foreach ($request->input('reports') as $item) {
            $nameOrTask = $item['name'];

            if (!$nameOrTask) continue;

            $project = Project::where('name', $nameOrTask)->first();

            if (null !== $project) {
                $project->last_used = time();
                $project->save();
            }

            $hours = abs(+$item['time']['hours']);
            $minutes = abs(+$item['time']['minutes']);

            if (null === $project && $item['isTracked']) {
                $project = Project::create([
                    'name' => $nameOrTask,
                    'last_used' => time(),
                ]);
            }

            if ($hours > 0 || $minutes > 0) {
                Report::create([
                    'user_id' => Auth::id(),
                    'project_id' => $project ? $project->id : null,
                    'task' => !$project ? $nameOrTask : null,
                    'date' => $date->format('Y-m-d'),
                    'worked_minutes' => $hours * 60 + $minutes,
                    'description' => $item['description'],
                    'is_tracked' => $item['isTracked'],
                ]);
            }
        }

        return response(null, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Report $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report $report
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        //
    }
}
