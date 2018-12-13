<?php namespace Common\Files\Response;

use Storage;
use Common\Files\FileEntry;
use Symfony\Component\HttpFoundation\Response;

class ImageResponse {

    /**
     * Create response for previewing specified image.
     * Optionally resize image to specified size.
     *
     * @param FileEntry $entry
     * @return Response
     */
    public function create(FileEntry $entry)
    {
        $content = $entry->getDisk()->get($entry->getStoragePath());
        return response($content, 200, ['Content-Type' => $entry->mime]);
    }
}