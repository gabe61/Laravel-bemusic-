<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getTrackTitle($track) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ $utils->getTrackUrl($track) }}">

    <meta itemprop="name" content="{{ $utils->getTrackTitle($track) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getTrackTitle($track) }}">
    <meta name="twitter:url" content="{{ $utils->getTrackUrl($track) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getTrackTitle($track) }}">
    <meta property="og:url" content="{{ $utils->getTrackUrl($track) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">

    <meta property="og:type" content="music.song">
    <meta property="music:duration" content="{{$track['duration'] * 1000}}">
    <meta property="music:album" content="{{$utils->getAlbumUrl($track['album'])}}">
    <meta property="music:album:track" content="{{$track['number']}}">

    @foreach($track['artists'] as $artist)
        <meta property="music:musician" content="{{$utils->getArtistUrlFromName($artist)}}">
    @endforeach

    <meta property="og:description" content="{{ $utils->getTrackDescription($track) }}">
    <meta itemprop="description" content="{{ $utils->getTrackDescription($track) }}">
    <meta property="description" content="{{ $utils->getTrackDescription($track) }}">
    <meta name="twitter:description" content="{{ $utils->getTrackDescription($track) }}">

    <meta itemprop="image" content="{{ $track['album']['image'] }}">
    <meta property="og:image" content="{{ $track['album']['image'] }}">
    <meta name="twitter:image" content="{{ $track['album']['image'] }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="300">
</head>

<body>
    <h1 class="title">{{$utils->getTrackTitle($track)}}</h1>

    {!! $utils->getTrackDescription($track) !!}
    <br>

    <img src="{{$track['album']['image']}}">
</body>
</html>
