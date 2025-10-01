<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportTasksJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public string $fileName;

    public function __construct(?string $fileName = null)
    {
        $this->fileName = $fileName ?? 'tasks_export_' . now()->format('Ymd_His') . '.csv';
    }

    public function handle(): void
    {
        $tasks = Task::with(['creator', 'assignedUser'])->get();

        $csvData = [];
        $csvData[] = ['ID', 'Title', 'Status', 'Priority', 'Due Date', 'Assigned User', 'Created By'];

        foreach ($tasks as $task) {
            $csvData[] = [
                $task->id,
                $task->title,
                $task->status,
                $task->priority,
                $task->due_date,
                optional($task->assignedUser)->name,
                optional($task->creator)->name,
            ];
        }

        // Ubah array ke CSV string
        $csvString = collect($csvData)
            ->map(fn($row) => implode(',', array_map(fn($v) => '"' . $v . '"', $row)))
            ->implode("\n");

        Storage::disk('local')->put('exports/' . $this->fileName, $csvString);
    }
}
