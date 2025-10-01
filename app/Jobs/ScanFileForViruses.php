<?php

namespace App\Jobs;

use App\Models\TaskAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanFileForViruses implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public TaskAttachment $attachment;

    public function __construct(TaskAttachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function handle(): void
    {
        // Simulasi scanning (delay 2 detik)
        sleep(2);

        // Update status file -> dianggap bersih
        $this->attachment->update([
            'is_clean' => true
        ]);
    }
}
