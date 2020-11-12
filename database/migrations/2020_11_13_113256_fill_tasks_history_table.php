<?php

use App\Models\Report;
use App\Models\TasksHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class FillTasksHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
        SET @task_rank := 0;
        SET @current_user := 0;
        SELECT user_id, task, max_date
        FROM
            (SELECT user_id, task, max_date,
                @task_rank := IF(@current_user = user_id, @task_rank + 1, 1) AS task_rank,
                @current_user := user_id
            FROM
                (SELECT user_id, task, MAX(date) AS max_date
                FROM reports
                WHERE is_tracked = 0 AND task IS NOT NULL
                GROUP BY user_id, task
                ) AS sub
            ORDER BY user_id, max_date DESC
            ) AS ranked
        WHERE task_rank <= 10;
     *
     * @return void
     */
    public function up()
    {
        DB::statement(DB::raw('SET @task_rank = 0'));
        DB::statement(DB::raw('SET @current_user = 0'));
        $subQuery = Report::select(DB::raw('user_id, task, MAX(date) AS max_date'))
            ->where('is_tracked', Report::REPORT_UNTRACKED)
            ->whereNotNull('task')
            ->groupBy(['user_id', 'task']);
        $rankedQuery = DB::table(DB::raw("({$subQuery->toSql()}) AS sub"))
            ->mergeBindings($subQuery->getQuery())
            ->addSelect(DB::raw('user_id, task, max_date,
            @task_rank := IF(@current_user = user_id, @task_rank + 1, 1) AS task_rank,
            @current_user := user_id'))
            ->orderBy('user_id', 'ASC')
            ->orderBy('max_date', 'DESC');
        $tasks = DB::table(DB::raw("({$rankedQuery->toSql()}) AS ranked"))
            ->mergeBindings($rankedQuery)
            ->addSelect(DB::raw('user_id, task, max_date'))
            ->where('task_rank', '<=', 10)
            ->get()
            ->toArray();
        $tasksArray = $tasks ? json_decode(json_encode($tasks), true) : [];
        if (!empty($tasksArray)) {
            TasksHistory::insert($tasksArray);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("SET foreign_key_checks=0");
        TasksHistory::truncate();
        DB::statement("SET foreign_key_checks=1");
    }
}
