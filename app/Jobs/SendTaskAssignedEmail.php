<?php

namespace App\Jobs;

use App\Mail\TaskAssignedMail;
use App\Models\Task; // ⬅️ WAJIB, ini yang hilang kalau error masih muncul
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Task $task; // ⬅️ type hint benar

    public function __construct(Task $task) // ⬅️ type hint benar
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
