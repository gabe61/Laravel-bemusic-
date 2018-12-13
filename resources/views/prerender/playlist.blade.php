<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getPlaylistTitle($data['playlist']) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ $utils->getPlaylistUrl($data['playlist']) }}">

    <meta itemprop="name" content="{{ $utils->getPlaylistTitle($data['playlist']) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getPlaylistTitle($data['playlist']) }}">
    <meta name="twitter:url" content="{{ $utils->getPlaylistUrl($data['playlist']) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getPlaylistTitle($data['playlist']) }}">
    <meta property="og:url" content="{{ $utils->getPlaylistUrl($data['playlist']) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">

    <meta property="og:type" content="music.playlist">

    <meta property="og:description" content="{{ $utils->getPlaylistDescription($data['playlist']) }}">
    <meta itemprop="description" content="{{ $utils->getPlaylistDescription($data['playlist']) }}">
    <meta property="description" content="{{ $utils->getPlaylistDescription($data['playlist']) }}">
    <meta name="twitter:description" content="{{ $utils->getPlaylistDescription($data['playlist']) }}">

    <meta itemprop="image" content="{{ $utils->getPlaylistImage($data['playlist'], $data['tracks']) }}">
    <meta property="og:image" content="{{ $utils->getPlaylistImage($data['playlist'], $data['tracks']) }}">
    <meta name="twitter:image" content="{{ $utils->getPlaylistImage($data['playlist'], $data['tracks']) }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="300">

    @foreach($data['tracks'] as $track)
        <meta property="music:song" content="{{ $utils->getTrackUrl($track) }}">
    @endforeach
</head>

<body>
    <h1 class="title">{{$utils->getPlaylistTitle($data['playlist'])}}</h1>

    {!! $utils->getPlaylistDescription($data['playlist']) !!}
    <br>

    <img src="{{$utils->getPlaylistImage($data['playlist'], $data['tracks'])}}">

    @foreach($data['tracks'] as $track)
        <li><a href="{{ $utils->getTrackUrl($track) }}">{{ $track['name'] }}</a></li>
    @endforeach
</body>
</html>
