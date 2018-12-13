<?php namespace App\Services;

use App\Album;
use App\Artist;
use App\Track;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CustomUploads {

    private $baseCustomImagePath;
    private $baseCustomMusicPath;
    private $baseCustomImageUrl;

    /**
     * Create new CustomUploads instance.
     */
    public function __construct()
    {
        $this->baseCustomImagePath = dirname(base_path()).'/assets/images';
        $this->baseCustomMusicPath = base_path('storage/app/music');
        $this->baseCustomImageUrl  = url('assets/images');
    }

    /**
     * Upload a custom image or stream file for given model.
     *
     * @param UploadedFile $file
     * @param Album|Artist|Track $model
     * @param null|string $type
     *
     * @return Album|Artist|Track
     */
    public function upload($file, $model, $type = null)
    {
        $modelName = $this->getModelName($model);

        if ($modelName === 'album') {
            return $this->uploadCustomAlbumImage($file, $model);
        }

        if ($modelName === 'artist') {
            return $this->uploadCustomArtistImage($file, $model, $type);
        }

        if ($modelName === 'track') {
            return $this->uploadCustomTrackFile($file, $model);
        }
    }

    /**
     * Upload a file to stream given track from.
     *
     * @param UploadedFile $file
     * @param Track $model
     * @return Track
     */
    private function uploadCustomTrackFile($file, $model)
    {
        if (file_put_contents($this->getCustomTrackFilePath($model), file_get_contents($file))) {
            $model->url = url('track/'.$model->id.'/'.$file->getClientOriginalExtension().'/stream');
            $model->save();
        }

        return $model;
    }

    /**
     * Upload custom image for given artist.
     *
     * @param UploadedFile $file
     * @param Artist $model
     * @param string $type
     *
     * @return Artist
     */
    private function uploadCustomArtistImage($file, $model, $type)
    {
        $fileName = $this->getCustomArtistImageNameHash($file, $model, $type);
        $path = $this->baseCustomImagePath.'/artists/'.$fileName;

        if (file_put_contents($path, file_get_contents($file))) {
            $model->$type = $this->baseCustomImageUrl.'/artists/'.$fileName;
            $model->save();
        }

        return $model;
    }

    /**
     * Upload custom image for given album.
     *
     * @param UploadedFile $file
     * @param Album $model
     *
     * @return Album
     */
    private function uploadCustomAlbumImage($file, $model)
    {
        $fileName = $this->getCustomAlbumImageNameHash($file, $model);
        $path = $this->baseCustomImagePath.'/albums/'.$fileName;

        if (file_put_contents($path, file_get_contents($file))) {
            $model->image = $this->baseCustomImageUrl.'/albums/'.$fileName;
            $model->save();
        }

        return $model;
    }

    /**
     * Delete a custom stream file uploaded for this track if it exists.
     *
     * @param Track $model
     */
    public function deleteCustomTrackFile($model)
    {
        if ($model['url']) {
            try { unlink($this->getCustomTrackFilePath($model)); } catch (\Exception $e) {}
        }
    }

    /**
     * Return fully qualified custom track stream file path.
     *
     * @param Track $model
     * @param bool  $relative
     * @return string
     */
    public function getCustomTrackFilePath($model, $relative = false)
    {
        $fileName = $this->getCustomTrackFileNameHash($model);

        if ($relative) {
            return 'music/'.$fileName;
        } else {
            return $this->baseCustomMusicPath .'/'. $fileName;
        }
    }

    /**
     * Delete custom images uploaded for this album.
     *
     * @param Album $model
     */
    public function deleteCustomAlbumImage($model)
    {
        if (str_contains($model->image, env('BASE_URL'))) {
            try {
                unlink($this->getAlbumImagePathFromModel($model));
            } catch (\Exception $e) {}
        }
    }

    /**
     * Delete custom images uploaded for this artist.
     *
     * @param Artist $model
     */
    public function deleteCustomArtistImages($model)
    {
        if (str_contains($model->image_small, env('BASE_URL'))) {
            try { unlink($this->getArtistImagePathFromModel($model, 'image_small')); } catch (\Exception $e) {}
        }

        if (str_contains($model->image_large, env('BASE_URL'))) {
            try { unlink($this->getArtistImagePathFromModel($model, 'image_large')); } catch (\Exception $e) {}
        }
    }

    /**
     * Get album image path in filesystem.
     *
     * @param Album $model
     * @return string
     */
    private function getAlbumImagePathFromModel($model)
    {
        return $this->baseCustomImagePath.'/albums/'.basename($model->image);
    }

    /**
     * Get artist image path in filesystem.
     *
     * @param Artist $model
     * @param string $type
     * @return string
     */
    private function getArtistImagePathFromModel($model, $type)
    {
        return $this->baseCustomImagePath.'/artists/'.basename($model->$type);
    }

    /**
     * Generate hashed file name for custom track stream file.
     *
     * @param Track $model
     * @return string
     */
    private function getCustomTrackFileNameHash($model)
    {
        return md5(Str::slug($model['artists'][0]).'_'.Str::slug($model['album_name']).'_'.Str::slug($model['name']));
    }

    /**
     * Generate hashed file name for custom album image.
     *
     * @param UploadedFile $file
     * @param Album $model
     * @return string
     */
    private function getCustomAlbumImageNameHash($file, $model)
    {
        return md5(Str::slug($model->artist->name).'_'.Str::slug($model->name).'_'.($model->release_date ? $model->release_date : $model->id)).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Generate hashed file name for custom artist image.
     *
     * @param UploadedFile $file
     * @param Artist $model
     * @param string $type
     *
     * @return string
     */
    private function getCustomArtistImageNameHash($file, $model, $type)
    {
        return md5(Str::slug($model->name).'_'.$type).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Get passed in models name.
     *
     * @param Album|Artist|Track $model
     * @return string
     */
    private function getModelName($model)
    {
        $namespace = get_class($model);
        return strtolower(substr($namespace, strrpos($namespace, '\\') + 1));
    }

}
