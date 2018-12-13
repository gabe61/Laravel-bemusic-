<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTitle('top_50') }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ url('top-50') }}">

    <meta itemprop="name" content="{{ $utils->getTitle('top_50') }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTitle('top_50') }}">
    <meta name="twitter:url" content="{{ url('top-50') }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTitle('top_50') }}">
    <meta property="og:url" content="{{ url('top-50') }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getDescription('top_50') }}">
    <meta itemprop="description" content="{{ $utils->getDescription('top_50') }}">
    <meta property="description" content="{{ $utils->getDescription('top_50') }}">
    <meta name="twitter:description" content="{{ $utils->getDescription('top_50') }}">
</head>

<body>
    <h1>{{ $utils->getTitle('top_50') }}</h1>

    <p>{{ $utils->getDescription('top_50') }}</p>

    <ul class="tracks">
        @foreach($data as $track)
            <li>
                <figure>
                    <img src="{{$track['album']['image']}}">
                    <figcaption>
                        <a href="{{$utils->getTrackUrl($track)}}">{{$track['name']}}</a> by
                        <a href="{{$utils->getArtistUrl($track['album']['artist'])}}">{{$track['album']['artist']['name']}}</a>
                    </figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
</body>
</html>
