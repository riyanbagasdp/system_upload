<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Task;
use App\Jobs\SendTaskAssignedEmail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth; // biar lebih singkat
use App\Jobs\BulkUpdateTaskStatus;   // ✅ tambahkan
use App\Jobs\ExportTasksJob;         // ✅ tambahkan
use Illuminate\Support\Facades\Storage; // ✅ tambahkan

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignee']);

        // Filtering
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Sorting
        if ($request->has('sortBy')) {
            $query->orderBy($request->sortBy, $request->get('direction', 'asc'));
        }

        $tasks = $query->paginate(10);
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'in:pending,in_progress,completed',
            'priority'         => 'in:low,medium,high',
            'assigned_user_id' => 'nullable|exists:users,id',
            'due_date'         => 'nullable|date',
        ]);

        // Ambil user login dari JWT
        $validated['created_by'] = JWTAuth::user()->id;

        $task = Task::create($validated);

        // 🚀 Dispatch email jika ada assigned user
        if (!empty($task->assigned_user_id)) {
            SendTaskAssignedEmail::dispatch($task);
        }

        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title'            => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'in:pending,in_progress,completed',
            'priority'         => 'in:low,medium,high',
            'assigned_user_id' => 'nullable|exists:users,id',
            'due_date'         => 'nullable|date',
        ]);

        // Simpan assigned_user_id lama
        $oldAssignee = $task->assigned_user_id;

        $task->update($validated);

        // 🚀 Kalau assigned user berubah, kirim email ke user baru
        if (
            array_key_exists('assigned_user_id', $validated) &&
            $validated['assigned_user_id'] &&
            $validated['assigned_user_id'] != $oldAssignee
        ) {
            SendTaskAssignedEmail::dispatch($task);
        }

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        BulkUpdateTaskStatus::dispatch($validated['task_ids'], $validated['status']);

        return response()->json([
            'message' => 'Bulk update job dispatched',
            'task_ids' => $validated['task_ids'],
            'status' => $validated['status']
        ]);
    }

    // ✅ Export tasks (CSV)
    public function exportTasks()
    {
        $fileName = 'tasks_export_' . now()->format('Ymd_His') . '.csv';

        ExportTasksJob::dispatch($fileName);

        return response()->json([
            'message' => 'Export job dispatched',
            'file' => $fileName,
            'download_url' => url('/api/tasks/export/download/' . $fileName)
        ]);
    }

    // ✅ Endpoint untuk download setelah job selesai
    public function downloadExport($file)
    {
        $filePath = 'exports/' . $file;

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // ✅ gunakan response()->download, bukan Storage::download
        return response()->download(storage_path("app/{$filePath}"));
    }
}
