<?php

namespace App\Jobs;

use App\Mail\TaskAssignedMail;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignedEmail implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle(): void
    {
        if ($this->task->assignedUser) {
            Mail::to($this->task->assignedUser->email)
                ->send(new TaskAssignedMail($this->task));
        }
    }
}
