<?php

namespace App\Repositories;

use App\Models\Auth\User;
use App\Models\TasksHistory as TasksHistoryModel;
use Illuminate\Support\Facades\DB;

class TasksHistory
{
    /**
     * @param User $user
     * @return array
     */
    public function getLatestTaskNames(User $user)
    {
        $subQuery = TasksHistoryModel::where('user_id', $user->id)
            ->orderBy('max_date', 'desc')
            ->limit(50);

        $latestTaskNames = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery->getQuery())
            ->orderBy('task', 'asc')
            ->get();
        foreach ($latestTaskNames as $latestTaskName) {
            $latestTaskNamesArray[] = ['id' => $latestTaskName->id, 'task' => $latestTaskName->task];
        }

        return !empty($latestTaskNamesArray) ? $latestTaskNamesArray : [];
    }

    /**
     * @param User $user
     * @return array
     */
    public function getTaskNames(User $user)
    {
        $taskNames = TasksHistoryModel::where('user_id', $user->id)
            ->orderBy('task', 'asc')
            ->get();
        foreach ($taskNames as $taskName) {
            $taskNamesArray[] = ['id' => $taskName->id, 'task' => $taskName->task];
        }

        return !empty($taskNamesArray) ? $taskNamesArray : [];
    }
}
