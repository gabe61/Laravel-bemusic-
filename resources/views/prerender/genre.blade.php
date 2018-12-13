<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTitle('genre', 'GENRE_NAME', $name) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ url('genre/'.$name) }}">

    <meta itemprop="name" content="{{ $utils->getTitle('genre', 'GENRE_NAME', $name) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTitle('genre', 'GENRE_NAME', $name) }}">
    <meta name="twitter:url" content="{{ url('genre/'.$name) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTitle('genre', 'GENRE_NAME', $name) }}">
    <meta property="og:url" content="{{ url('genre/'.$name) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getDescription('genre', 'GENRE_DESCRIPTION', $name) }}">
    <meta itemprop="description" content="{{ $utils->getDescription('genre', 'GENRE_DESCRIPTION', $name) }}">
    <meta property="description" content="{{ $utils->getDescription('genre', 'GENRE_DESCRIPTION', $name) }}">
    <meta name="twitter:description" content="{{ $utils->getDescription('genre', 'GENRE_DESCRIPTION', $name) }}">
</head>

<body>
    <h1>{{ $utils->getTitle('genre', 'GENRE_NAME', $name) }}</h1>

    <ul class="artists">
        @foreach($data['artistsPagination']['data'] as $artist)
            <li>
                <figure>
                    <img src="{{$artist['image_small']}}">
                    <figcaption><a href="{{$utils->getArtistUrl($artist)}}">{{$artist['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
</body>
</html>
