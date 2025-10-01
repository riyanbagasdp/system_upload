<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Jobs\ScanFileForViruses;


class AttachmentController extends Controller
{
    public function upload(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,mov,avi',
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $thumbnailPath = null;

        // Generate thumbnail hanya untuk gambar
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $thumbnail = Image::read($file)
                ->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                });

            $thumbName = 'thumbnails/' . uniqid() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($thumbName, (string) $thumbnail->encode());
            $thumbnailPath = $thumbName;
        }

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        // Dispatch virus scanning job
        ScanFileForViruses::dispatch($attachment);

        return response()->json($attachment, 201);
    }
}
