<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileTrait
{
    public function uploadFile($file, $path)
    {
        $path = Str::of($path)->replace(' ', '')->replace('-', '_');
        $fileNameToStore = $path;
        $uploadedPath = Storage::disk('public')->put($fileNameToStore, $file);
        $uploadedPath = Storage::disk('public')->url($uploadedPath);
        return $uploadedPath;
    }

    function deleteFile($filePath)
    {
        $cleanedPath = str_replace(config('app.url'), '', $filePath);
        $relativePath = ltrim(str_replace('/storage/', '', $cleanedPath), '/');
        Storage::disk('public')->delete($relativePath);
    }
}
