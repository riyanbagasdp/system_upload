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
            // Jika kamu menggunakan Intervention Image pakai make()
            $thumbnail = Image::make($file)
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

    public function download($id)
    {
        $attachment = TaskAttachment::findOrFail($id);
        $disk = Storage::disk('public');

        if (!$disk->exists($attachment->file_path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Jika disk lokal memiliki path() -> gunakan response()->download()
        if (method_exists($disk, 'path')) {
            $fullPath = $disk->path($attachment->file_path);
            return response()->download($fullPath, $attachment->file_name);
        }

        // Fallback: stream dari disk (untuk S3 / remote)
        $stream = $disk->readStream($attachment->file_path);
        if ($stream === false) {
            return response()->json(['error' => 'Unable to read file'], 500);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            'Content-Length' => $attachment->file_size ? (string) $attachment->file_size : '',
            'Content-Disposition' => 'attachment; filename="' . $attachment->file_name . '"',
        ]);
    }

    public function destroy($id)
    {
        $attachment = TaskAttachment::findOrFail($id);

        // Hapus file utama
        $disk = Storage::disk('public');
        if ($attachment->file_path && $disk->exists($attachment->file_path)) {
            $disk->delete($attachment->file_path);
        }

        // Hapus thumbnail jika ada
        if ($attachment->thumbnail_path && $disk->exists($attachment->thumbnail_path)) {
            $disk->delete($attachment->thumbnail_path);
        }

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }
}
