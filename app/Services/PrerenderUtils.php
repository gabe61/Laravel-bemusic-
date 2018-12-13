<?php namespace App\Services;

use App\Album;
use App\Artist;
use App\Playlist;
use App\Track;
use App\User;
use Illuminate\Http\Request;
use Common\Settings\Settings;

class PrerenderUtils
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * PrerenderUtils constructor.
     *
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    /**
     * Get site name setting.
     *
     * @return string
     */
    public function getSiteName()
    {
        return $this->settings->get('branding.site_name');
    }

    /**
     * Get artist seo title.
     *
     * @param Artist $artist
     * @return string
     */
    public function getArtistTitle($artist)
    {
        $title = $this->settings->get("seo.artist_title");
        return $this->replacePlaceholder('ARTIST_NAME', $artist['name'], $title);
    }

    /**
     * Get artist seo description.
     *
     * @param Artist $artist
     * @return string
     */
    public function getArtistDescription($artist)
    {
        $description = $this->settings->get("seo.artist_description");

        if (isset($artist['bio']['bio'])) {
            $stripped = str_limit($artist['bio']['bio'], 160);
        } else {
            $stripped = '';
        }


        return $this->replacePlaceholder('ARTIST_DESCRIPTION', $stripped, $description);
    }

    /**
     * Get album seo title.
     *
     * @param Album $album
     * @return string
     */
    public function getAlbumTitle($album)
    {
        $title = $this->settings->get("seo.album_title");
        $title = $this->replacePlaceholder('ALBUM_NAME', $album['name'], $title);
        $title = $this->replacePlaceholder('SITE_NAME', $this->getSiteName(), $title);

        if (isset($album['artist']['name'])) {
            $title = $this->replacePlaceholder('ARTIST_NAME', $album['artist']['name'], $title);
        }

        return $title;
    }

    /**
     * Get album seo description.
     *
     * @param Album $album
     * @return string
     */
    public function getAlbumDescription($album)
    {
        $description = $this->settings->get("seo.album_description");
        $description = $this->replacePlaceholder('ALBUM_NAME', $album['name'], $description);
        $description = $this->replacePlaceholder('ARTIST_NAME', $album['artist']['name'], $description);
        return $this->replacePlaceholder('SITE_NAME', $this->getSiteName(), $description);
    }

    /**
     * Get track seo title.
     *
     * @param Track $track
     * @return string
     */
    public function getTrackTitle($track)
    {
        $title = $this->settings->get("seo.track_title");
        $title = $this->replacePlaceholder('TRACK_NAME', $track['name'], $title);

        if (isset($track['artists'][0])) {
            $title = $this->replacePlaceholder('ARTIST_NAME', $track['artists'][0], $title);
        }

        return $title;
    }

    /**
     * Get track seo description.
     *
     * @param Track $track
     * @return string
     */
    public function getTrackDescription($track)
    {
        $description = $this->settings->get("seo.track_description");
        $description = $this->replacePlaceholder('TRACK_NAME', $track['name'], $description);
        $description = $this->replacePlaceholder('ARTIST_NAME', $track['album']['artist']['name'], $description);
        return $this->replacePlaceholder('SITE_NAME', $this->getSiteName(), $description);
    }

    /**
     * Get playlist seo title.
     *
     * @param Playlist $playlist
     * @return string
     */
    public function getPlaylistTitle($playlist)
    {
        $title = $this->settings->get("seo.playlist_title");
        $title = $this->replacePlaceholder('PLAYLIST_NAME', $playlist['name'], $title);
        $title = $this->replacePlaceholder('CREATOR_NAME', $playlist['editors'][0]['display_name'], $title);
        return $this->replacePlaceholder('SITE_NAME', $this->getSiteName(), $title);
    }

    /**
     * Get playlist seo description.
     *
     * @param Playlist $playlist
     * @return string
     */
    public function getPlaylistDescription($playlist)
    {
        $description = $this->settings->get("seo.playlist_description");
        return $this->replacePlaceholder('PLAYLIST_DESCRIPTION', $playlist['description'], $description);
    }

    /**
     * Get playlist image or first album image.
     *
     * @param Playlist $playlist
     * @param array $tracks
     * @return string
     */
    public function getPlaylistImage($playlist, $tracks = []) {
        if ($playlist['image']) return $playlist['image'];
        if (isset($tracks[0]['album']['image'])) return $tracks[0]['album']['image'];
        return url('assets/images/default/album.png');
    }

    /**
     * Get search page seo title.
     *
     * @param string $query
     * @return string
     */
    public function getSearchTitle($query)
    {
        $title = $this->settings->get("seo.search_title");
        return $this->replacePlaceholder('QUERY', $query, $title);
    }

    /**
     * Get search page seo description.
     *
     * @param string $query
     * @return string
     */
    public function getSearchDescription($query)
    {
        $description = $this->settings->get("seo.search_description");
        return $this->replacePlaceholder('QUERY', $query, $description);
    }

    /**
     * Get user page seo title.
     *
     * @param User $user
     * @return string
     */
    public function getUserTitle($user)
    {
        $title = $this->settings->get("seo.user_title");
        return $this->replacePlaceholder('DISPLAY_NAME', $user['display_name'], $title);
    }

    /**
     * Get user page seo description.
     *
     * @param User $user
     * @return string
     */
    public function getUserDescription($user)
    {
        $description = $this->settings->get("seo.user_description");
        $description = $this->replacePlaceholder('DISPLAY_NAME', $user['display_name'], $description);
        return $this->replacePlaceholder('SITE_NAME', $this->getSiteName(), $description);
    }

    /**
     * Get search page seo title.
     *
     * @param string $name
     * @param string $find
     * @param string $replace
     * @return string
     */
    public function getTitle($name, $find = null, $replace = null)
    {
        $title = $this->settings->get("seo.{$name}_title");

        if ($find && $replace) {
            $title = $this->replacePlaceholder($find, $replace, $title);
        }

        return $title;
    }

    /**
     * Get search page seo description.
     *
     * @param string $name
     * @param string $find
     * @param string $replace
     * @return string
     */
    public function getDescription($name, $find = null, $replace = null)
    {
        $description = $this->settings->get("seo.{$name}_description");

        if ($find && $replace) {
           $description =  $this->replacePlaceholder($find, $replace, $description);
        }

        return $description;
    }

    /**
     * Get help center homepage seo title.
     *
     * @return string
     */
    public function getHomeTitle()
    {
        return $this->settings->get("seo.home_title");
    }

    /**
     * Get help center homepage seo description.
     *
     * @return string
     */
    public function getHomeDescription()
    {
        return $this->settings->get("seo.home_description");
    }

    /**
     * Get absolute url for specified artist.
     *
     * @param array $artist
     * @return string
     */
    public function getArtistUrl($artist)
    {
        return url("artist/{$artist['id']}/".$artist['name']);
    }

    /**
     * Get absolute url for specified artist.
     *
     * @param string $artist
     * @return string
     */
    public function getArtistUrlFromName($artist)
    {
        return url('artist/'.$artist);
    }

    /**
     * Get absolute url for specified album.
     *
     * @param array $album
     * @return string
     */
    public function getAlbumUrl($album)
    {
        $uri = "album/{$album['id']}/";
        $uri .= $album['artist'] ? $album['artist']['name'].'/'.$album['name'] : $album['name'];
        return url($uri);
    }

    /**
     * Get absolute url for specified track.
     *
     * @param Track $track
     * @return string
     */
    public function getTrackUrl($track)
    {
        return url("track/{$track['id']}/".$track['name']);
    }

    /**
     * Get absolute url for specified playlist.
     *
     * @param Playlist $playlist
     * @return string
     */
    public function getPlaylistUrl($playlist)
    {
        return url("playlists/{$playlist['id']}/".$playlist['name']);
    }

    /**
     * Get absolute url for specified search query.
     *
     * @param string $query
     * @return string
     */
    public function getSearchUrl($query)
    {
        return url("search/".$query);
    }

    /**
     * Get absolute url for specified user.
     *
     * @param User $user
     * @return string
     */
    public function getUserUrl($user)
    {
        return url("user/{$user['id']}".$user['display_name']);
    }

    /**
     * Get absolute url for help center homepage.
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return url('help-center');
    }

    /**
     * Replace placeholder with actual value in specified string.
     *
     * @param string $key
     * @param string $value
     * @param string $subject
     * @return string
     */
    private function replacePlaceholder($key, $value, $subject) {
        return str_replace('{{'.$key.'}}', $value, $subject);
    }
}