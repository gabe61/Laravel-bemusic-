<?php

namespace Common\Files\Actions\Storage;

use Illuminate\Http\UploadedFile;
use Storage;
use Common\Files\FileEntry;

class StorePrivateUpload
{
    /**
     * @param FileEntry $entry
     * @param UploadedFile|string $contents
     */
    public function execute(FileEntry $entry, $contents)
    {
        $disk = Storage::disk(config('common.site.uploads_disk'));

        if (is_a($contents, UploadedFile::class)) {
            $disk->putFileAs($entry->file_name, $contents, $entry->file_name);
        } else {
            $disk->put("{$entry->file_name}/{$entry->file_name}", $contents);
        }
    }
}