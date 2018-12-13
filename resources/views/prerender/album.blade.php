<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getAlbumTitle($album) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ $utils->getAlbumUrl($album) }}">

    <meta itemprop="name" content="{{ $utils->getAlbumTitle($album) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getAlbumTitle($album) }}">
    <meta name="twitter:url" content="{{ $utils->getAlbumUrl($album) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getAlbumTitle($album) }}">
    <meta property="og:url" content="{{ $utils->getAlbumUrl($album) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">

    <meta property="og:type" content="music.album">
    <meta property="music:release_date" content="{{$album['release_date']}}">
    <meta property="music:musician" content="{{$utils->getArtistUrl($album['artist'])}}">

    <meta property="og:description" content="{{ $utils->getAlbumDescription($album) }}">
    <meta itemprop="description" content="{{ $utils->getAlbumDescription($album) }}">
    <meta property="description" content="{{ $utils->getAlbumDescription($album) }}">
    <meta name="twitter:description" content="{{ $utils->getAlbumDescription($album) }}">

    <meta itemprop="image" content="{{ $album['image'] }}">
    <meta property="og:image" content="{{ $album['image'] }}">
    <meta name="twitter:image" content="{{ $album['image'] }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="300">

    @foreach($album['tracks'] as $track)
        <meta property="music:song" content="{{ $utils->getTrackUrl($track) }}">
        <meta property="music:song:track" content="{{ $track['number '] }}">
    @endforeach
</head>

<body>
    <h1 class="title">{{$utils->getAlbumTitle($album)}} by {{$album['artist']['name']}}</h1>

    {!! $utils->getAlbumDescription($album) !!}
    <br>

    <img src="{{$album['image']}}">

    <h2>{{$utils->getAlbumTitle($album)}} - {{$album['release_date']}}</h2>

    @foreach($album['tracks'] as $track)
        <li><a href="{{ $utils->getTrackUrl($track) }}">{{ $track['name'] }}</a></li>
    @endforeach
</body>
</html>
