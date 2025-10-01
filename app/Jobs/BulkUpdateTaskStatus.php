<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkUpdateTaskStatus implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $taskIds;
    public $status;

    public function __construct(array $taskIds, string $status)
    {
        $this->taskIds = $taskIds;
        $this->status = $status;
    }

    public function handle(): void
    {
        Task::whereIn('id', $this->taskIds)
            ->update(['status' => $this->status]);
    }
}
