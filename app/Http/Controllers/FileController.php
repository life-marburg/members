<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    private function resolvePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return '';
        }

        $normalized = realpath(Storage::disk('shared')->path($path));
        $root = realpath(Storage::disk('shared')->path(''));

        if ($normalized === false || $root === false) {
            return null;
        }

        if (!str_starts_with($normalized, $root)) {
            return null;
        }

        return $path;
    }

    public function index(?string $path = null)
    {
        $resolved = $this->resolvePath($path);

        if ($resolved === null) {
            abort(404);
        }

        $disk = Storage::disk('shared');
        $directories = $disk->directories($resolved);
        $files = $disk->files($resolved);

        // Strip the current path prefix to get just the names
        $prefix = $resolved !== '' ? $resolved . '/' : '';

        $folders = collect($directories)
            ->map(fn (string $dir) => [
                'name' => str_replace($prefix, '', $dir),
                'path' => $dir,
            ])
            ->sortBy('name')
            ->values();

        $fileList = collect($files)
            ->map(fn (string $file) => [
                'name' => str_replace($prefix, '', $file),
                'path' => $file,
                'size' => $disk->size($file),
            ])
            ->sortBy('name')
            ->values();

        // Build breadcrumbs
        $breadcrumbs = [['name' => __('Files'), 'path' => null]];
        if ($resolved !== '') {
            $parts = explode('/', $resolved);
            $cumulative = '';
            foreach ($parts as $part) {
                $cumulative = $cumulative !== '' ? $cumulative . '/' . $part : $part;
                $breadcrumbs[] = ['name' => $part, 'path' => $cumulative];
            }
        }

        return view('pages.files', [
            'folders' => $folders,
            'files' => $fileList,
            'breadcrumbs' => $breadcrumbs,
            'currentPath' => $resolved,
        ]);
    }

    public function download(string $path): StreamedResponse
    {
        $resolved = $this->resolvePath($path);

        if ($resolved === null || !Storage::disk('shared')->exists($resolved)) {
            abort(404);
        }

        return Storage::disk('shared')->download($resolved);
    }
}
