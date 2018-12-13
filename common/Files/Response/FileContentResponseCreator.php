<?php namespace Common\Files\Response;

use Storage;
use Response;
use Common\Files\FileEntry;

class FileContentResponseCreator {

    /**
     * ImageResponse service instance.
     *
     * @var ImageResponse
     */
    private $imageResponse;

    /**
     * AudioVideoResponse service instance.
     *
     * @var AudioVideoResponse
     */
    private $audioVideoResponse;

    /**
     * FileContentResponse constructor.
     *
     * @param ImageResponse $imageResponse
     * @param AudioVideoResponse $audioVideoResponse
     */
    public function __construct(ImageResponse $imageResponse, AudioVideoResponse $audioVideoResponse)
    {
        $this->imageResponse = $imageResponse;
        $this->audioVideoResponse = $audioVideoResponse;
    }

    /**
     * Return download or preview response for given file.
     *
     * @param FileEntry  $upload
     *
     * @return mixed
     */
    public function create(FileEntry $upload)
    {
        if ( ! $upload->getDisk()->exists($upload->getStoragePath())) abort(404);

        list($mime, $type) = $this->getTypeFromModel($upload);

        if ($type === 'image') {
            return $this->imageResponse->create($upload);
        } elseif ($this->shouldStream($mime, $type)) {
            return $this->audioVideoResponse->create($upload);
        } else {
            return $this->createBasicResponse($upload);
        }
    }

    /**
     * Create a basic response for specified upload content.
     *
     * @param FileEntry $upload
     * @return Response
     */
    private function createBasicResponse(FileEntry $upload)
    {
        return response($upload->getDisk()->get($upload->getStoragePath()), 200, ['Content-Type' => $upload->mime]);
    }

    /**
     * Extract file type from model.
     *
     * @param FileEntry $fileModel
     * @return array
     */
    private function getTypeFromModel(FileEntry $fileModel)
    {
        $mime = $fileModel->mime;
        $type = explode('/', $mime)[0];

        return array($mime, $type);
    }

    /**
     * Should file with given mime be streamed.
     *
     * @param string $mime
     * @param string $type
     *
     * @return bool
     */
    private function shouldStream($mime, $type) {
        return $type === 'video' || $type === 'audio' || $mime === 'application/ogg';
    }
}