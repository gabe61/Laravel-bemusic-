<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTitle('popular_genres') }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ url('popular-genres') }}">

    <meta itemprop="name" content="{{ $utils->getTitle('popular_genres') }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTitle('popular_genres') }}">
    <meta name="twitter:url" content="{{ url('popular_genres') }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTitle('popular_genres') }}">
    <meta property="og:url" content="{{ url('popular_genres') }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getDescription('popular_genres') }}">
    <meta itemprop="description" content="{{ $utils->getDescription('popular_genres') }}">
    <meta property="description" content="{{ $utils->getDescription('popular_genres') }}">
    <meta name="twitter:description" content="{{ $utils->getDescription('popular_genres') }}">
</head>

<body>
    <h1>{{ $utils->getTitle('popular_genres') }}</h1>

    <p>{{ $utils->getDescription('popular_genres') }}</p>

    <ul class="genres">
        @foreach($data as $genre)
            <li>
                <figure>
                    <img src="{{url('client/assets/'.$genre['image'])}}">
                    <figcaption><a href="{{ url('genre/'.$genre['name']) }}">{{$genre['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
</body>
</html>
