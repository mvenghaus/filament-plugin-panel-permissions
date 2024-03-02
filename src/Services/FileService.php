<?php

declare(strict_types=1);

namespace Mvenghaus\PanelPermissions\Services;

use Illuminate\Support\Facades\File;

class FileService
{
    public function write(string $file, string $content): void
    {
        File::makeDirectory(dirname($file), 0755, true, true);
        File::put($file, $content);
    }
}