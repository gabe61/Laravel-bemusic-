<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTitle('new_releases') }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ url('new-releases') }}">

    <meta itemprop="name" content="{{ $utils->getTitle('new_releases') }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTitle('new_releases') }}">
    <meta name="twitter:url" content="{{ url('new-releases') }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTitle('new_releases') }}">
    <meta property="og:url" content="{{ url('new-releases') }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getDescription('new_releases') }}">
    <meta itemprop="description" content="{{ $utils->getDescription('new_releases') }}">
    <meta property="description" content="{{ $utils->getDescription('new_releases') }}">
    <meta name="twitter:description" content="{{ $utils->getDescription('new_releases') }}">
</head>

<body>
    <h1>{{ $utils->getTitle('new_releases') }}</h1>

    <p>{{ $utils->getDescription('new_releases') }}</p>

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
