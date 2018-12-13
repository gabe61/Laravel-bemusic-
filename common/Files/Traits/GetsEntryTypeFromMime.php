<?php

namespace Common\Files\Traits;


trait GetsEntryTypeFromMime
{
    /**
     * Get type of file entry from specified mime.
     *
     * @param string $mime
     * @return string
     */
    protected function getTypeFromMime($mime)
    {
        $default = explode('/', $mime)[0];

        switch ($mime) {
            case 'application/x-zip-compressed':
            case 'application/zip':
                return 'archive';
            case 'application/pdf':
                return 'pdf';
            case 'vnd.android.package-archive':
                return 'android package';
            case str_contains($mime, 'xml');
                return 'spreadsheet';
            case str_contains($mime, 'photoshop');
                return 'photoshop';
            default:
                return $default === 'application' ? 'file' : $default;
        }
    }
}