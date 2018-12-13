<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getSearchTitle($query) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ $utils->getSearchUrl($query) }}">

    <meta itemprop="name" content="{{ $utils->getSearchTitle($query) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getSearchTitle($query) }}">
    <meta name="twitter:url" content="{{ $utils->getSearchUrl($query) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getSearchTitle($query) }}">
    <meta property="og:url" content="{{ $utils->getSearchUrl($query) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getSearchDescription($query) }}">
    <meta itemprop="description" content="{{ $utils->getSearchDescription($query) }}">
    <meta property="description" content="{{ $utils->getSearchDescription($query) }}">
    <meta name="twitter:description" content="{{ $utils->getSearchDescription($query) }}">
</head>

<body>
    <h1>{{ $utils->getSearchTitle($query) }}</h1>

    <h2>Artists</h2>
    <ul class="artists">
        @foreach($results['artists'] as $artist)
            <li>
                <figure>
                    <img src="{{$artist['image_small']}}">
                    <figcaption><a href="{{$utils->getArtistUrl($artist)}}">{{$artist['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>

    <h2>Albums</h2>
    <ul class="albums">
        @foreach($results['albums'] as $album)
            <li>
                <figure>
                    <img src="{{$album['image']}}">
                    <figcaption><a href="{{$utils->getAlbumUrl($album)}}">{{$album['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>

    <h2>Tracks</h2>
    <ul class="tracks">
        @foreach($results['tracks'] as $track)
            @isset($track['album'])
                <li>
                    <figure>
                        <img src="{{$album['album']['image']}}">
                        <figcaption>
                            <a href="{{$utils->getTrackUrl($track)}}">{{$track['name']}}</a> by
                            <a href="{{$utils->getArtistUrl($track['album']['artist'])}}">{{$track['album']['artist']['name']}}</a>
                        </figcaption>
                    </figure>
                </li>
            @endisset
        @endforeach
    </ul>

    <h2>Playlists</h2>
    <ul class="playlists">
        @foreach($results['playlists'] as $playlist)
            <li>
                <figure>
                    <img src="{{$utils->getPlaylistImage($playlist)}}">
                    <figcaption><a href="{{$utils->getPlaylistUrl($playlist)}}">{{$playlist['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>

    <h2>Users</h2>
    <ul class="users">
        @foreach($results['users'] as $user)
            <li>
                <figure>
                    <img src="{{$user['avatar']}}">
                    <figcaption><a href="{{$utils->getUserUrl($user)}}">{{$user['display_name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
</body>
</html>
