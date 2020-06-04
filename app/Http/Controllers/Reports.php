<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Project as ProjectTransformer;
use App\Service\Reports as ReportsService;

class Reports extends Controller
{
    const MAX_ALLOWED_MINUTES = 900; //15 hours
    private $stats;

    public function __construct(\App\Service\Statistics $statistics)
    {
        $this->stats = $statistics;
    }

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
     * @param ProjectTransformer $projectTransformer
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(ProjectTransformer $projectTransformer)
    {
        $latestProjects = Project::select()
            ->whereIn('id', Report::findLatestTracked(Auth::user())->select('project_id')->get())
            ->where('is_active', '=', 1)
            ->get();

        $latestProjects = $projectTransformer->transformCollection($latestProjects);

        return view(
            'reports.create',
            [
                'latestProjects' => $latestProjects,
                'js' => [
                    'searchProjectUrl' => route('projects.search'),
                    'latestProjects' => $latestProjects,
                ]
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Service\Skills $skillsService
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, \App\Service\Skills $skillsService)
    {
        $date = Carbon::parse($request->input('date'));

        if ($date->greaterThan(Carbon::today())) {
            return response()->json(['error' => 'Date can\'t be greater than today'], 400);
        }

        $delayed = [];

        $totalMinutes = $this->stats->getTotalLoggedMinutes(Auth::user(), $date);

        foreach ($request->input('reports') as $item) {
            $projectId = $taskName = $project = null;
            if ($item['isTracked']) {
                $projectId = (int)$item['name'];
            } else {
                $taskName = htmlspecialchars($item['name']);
            }

            if (!(isset($projectId) || isset($taskName))) {
                continue;
            }

            if (isset($projectId)) {
                $project = Project::where('id', $projectId)->first();
                if (isset($project)) {
                    $project->last_used = time();
                    $project->save();
                } else {
                    return response()->json(['error' => 'Выберите проект из списка'], 400);
                }
                $isProjectHasChildren =  !$project->parent_id && $project->children()->count();
                if ($isProjectHasChildren || !$project->is_active) {
                    return response()->json(['error' => 'Выберите проект из списка'], 400);
                }
            }

            $hours = abs(+$item['time']['hours']);
            $minutes = abs(+$item['time']['minutes']);

            $totalMinutes += $hours * 60 + $minutes;

            if ($totalMinutes >= static::MAX_ALLOWED_MINUTES) {
                $msg = trans('reports.unable_to_add_time', ['date' => $date->format('Y-m-d')]);
                return response()->json(['error' => $msg], 400);
            }

            // we will handle this later b/c we have to ensure
            // that total time is not exceeded max allowed value prior to save
            $delayed[] = compact('hours', 'minutes', 'project', 'taskName', 'item', 'date');
        }

        foreach ($delayed as $payload) {
            extract($payload);

            if ($hours > 0 || $minutes > 0) {
                $report = Report::create([
                    'user_id' => Auth::id(),
                    'project_id' => isset($project) ? $project->id : null,
                    'task' => !isset($project) ? $taskName : null,
                    'date' => $date->format('Y-m-d'),
                    'worked_minutes' => $hours * 60 + $minutes,
                    'is_meeting' => $item['isMeeting'],
                    'description' => $item['description'],
                    'is_tracked' => $item['isTracked'],
                    'is_overtime' => $item['isOvertime'],
                ]);
                if ($report->is_tracked && $report->user->is_revenue_required) {
                    $skillsService->addProjectToSkillsService($report);
                }
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
        if ($report->user_id !== Auth::id()) {
            return response()->json(['error' => 'Permission denied'], 400);
        }

        $report->delete();

        return response()->json(['success' => 'Report has been deleted.'], 200);
    }

    public function getMonthStats(Request $request, ReportsService $reportsService)
    {
        $date = new Carbon($request->get('date'));
        $monthStatsDTO = $reportsService->getMonthStatsDTO($date);

        return response()->json($monthStatsDTO);
    }
}
