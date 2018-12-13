<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTitle('homepage') }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ url('') }}">

    <meta itemprop="name" content="{{ $utils->getTitle('homepage') }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTitle('homepage') }}">
    <meta name="twitter:url" content="{{ url('') }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTitle('homepage') }}">
    <meta property="og:url" content="{{ url('') }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">
    <meta property="og:type" content="website">

    <meta property="og:description" content="{{ $utils->getDescription('homepage') }}">
    <meta itemprop="description" content="{{ $utils->getDescription('homepage') }}">
    <meta property="description" content="{{ $utils->getDescription('homepage') }}">
    <meta name="twitter:description" content="{{ $utils->getDescription('homepage') }}">
</head>

<body>
    <h1>{{ $utils->getTitle('homepage') }}</h1>

    <p>{{ $utils->getDescription('homepage') }}</p>

    <ul class="genres">
        <li>
            <a href="{{ url('new-releases') }}">New Releases</a>
            <a href="{{ url('popular-genres') }}">Popular Genres</a>
            <a href="{{ url('popular-albums') }}">Popular Albums</a>
            <a href="{{ url('top-50') }}">Top 50</a>
        </li>
    </ul>

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
