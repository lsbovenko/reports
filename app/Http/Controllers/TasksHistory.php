<?php

namespace App\Http\Controllers;

use App\Models\TasksHistory as TasksHistoryModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TasksHistory extends Controller
{
    /**
     * @param string $taskId
     * @return JsonResponse
     */
    public function remove($taskId)
    {
        $task = TasksHistoryModel::where('id', $taskId);

        if (!$task->exists()) {
            return response()->json(['error' => 'Task not found.'], 404);
        }
        if ($task->first()->user_id != Auth::id()) {
            return response()->json(['error' => 'Permission denied.'], 400);
        }
        $task->delete();

        return response()->json(['success' => 'Task removed from history.']);
    }
}
