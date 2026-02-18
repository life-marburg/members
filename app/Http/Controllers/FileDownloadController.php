<?php

namespace App\Http\Controllers;

use App\Services\SharedFolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileDownloadController extends Controller
{
    public function download(Request $request, SharedFolderService $service)
    {
        $path = $request->query('path');

        if (! $path || str_contains($path, '..')) {
            abort(404);
        }

        if (! Storage::disk('shared')->exists($path)) {
            abort(404);
        }

        if (! $service->canAccess($request->user(), $path)) {
            abort(403);
        }

        return response()->download(
            Storage::disk('shared')->path($path),
            basename($path)
        );
    }
}
