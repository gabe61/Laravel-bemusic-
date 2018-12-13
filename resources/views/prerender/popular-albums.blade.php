<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTitle('popular_albums') }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ url('popular-albums') }}">

    <meta itemprop="name" content="{{ $utils->getTitle('popular_albums') }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTitle('popular_albums') }}">
    <meta name="twitter:url" content="{{ url('popular-albums') }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTitle('popular_albums') }}">
    <meta property="og:url" content="{{ url('popular-albums') }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getDescription('popular_albums') }}">
    <meta itemprop="description" content="{{ $utils->getDescription('popular_albums') }}">
    <meta property="description" content="{{ $utils->getDescription('popular_albums') }}">
    <meta name="twitter:description" content="{{ $utils->getDescription('popular_albums') }}">
</head>

<body>
    <h1>{{ $utils->getTitle('popular_albums') }}</h1>

    <p>{{ $utils->getDescription('popular_albums') }}</p>

    <ul class="albums">
        @foreach($data as $album)
            <li>
                <figure>
                    <img src="{{$album['image']}}">
                    <figcaption><a href="{{$utils->getAlbumUrl($album)}}">{{$album['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
</body>
</html>
