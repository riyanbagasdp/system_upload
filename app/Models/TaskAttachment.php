<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id',
        'file_name',
        'file_path',
        'thumbnail_path',
        'file_size',
        'mime_type',
    ];

    public $timestamps = false;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
