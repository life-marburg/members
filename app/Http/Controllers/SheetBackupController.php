<?php

namespace App\Http\Controllers;

use App\Models\SheetBackup;
use App\Rights;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SheetBackupController extends Controller
{
    public function download(SheetBackup $backup): StreamedResponse
    {
        abort_unless(auth()->user()->hasRole(Rights::R_ADMIN), 403);
        abort_unless($backup->status === SheetBackup::STATUS_READY, 404);
        abort_unless(Storage::disk('sheet-backups')->exists($backup->file_path), 404);

        Log::info('Sheet backup download', [
            'backup_id' => $backup->id,
            'user_id' => auth()->id(),
        ]);

        return Storage::disk('sheet-backups')->download($backup->file_path);
    }
}
