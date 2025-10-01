<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskComment;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TaskCommentController extends Controller
{
    public function index($taskId)
    {
        $task = Task::findOrFail($taskId);
        $comments = TaskComment::with('user')
            ->where('task_id', $task->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => JWTAuth::user()->id,
            'comment' => $validated['comment'],
        ]);

        return response()->json($comment, 201);
    }
}
