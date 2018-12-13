<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getArtistTitle($data['artist']) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ $utils->getArtistUrl($data['artist']) }}">

    <meta itemprop="name" content="{{ $utils->getArtistTitle($data['artist']) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getArtistTitle($data['artist']) }}">
    <meta name="twitter:url" content="{{ $utils->getArtistUrl($data['artist']) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getArtistTitle($data['artist']) }}">
    <meta property="og:url" content="{{ $utils->getArtistUrl($data['artist']) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">

    <meta property="og:type" content="music.musician">

    <meta property="og:description" content="{{ $utils->getArtistDescription($data['artist']) }}">
    <meta itemprop="description" content="{{ $utils->getArtistDescription($data['artist']) }}">
    <meta property="description" content="{{ $utils->getArtistDescription($data['artist']) }}">
    <meta name="twitter:description" content="{{ $utils->getArtistDescription($data['artist']) }}">

    <meta itemprop="image" content="{{ $data['artist']['image_large'] }}">
    <meta property="og:image" content="{{ $data['artist']['image_large'] }}">
    <meta name="twitter:image" content="{{ $data['artist']['image_large'] }}">
    <meta property="og:image:width" content="1000">
    <meta property="og:image:height" content="667">
</head>

<body>
    <h1 class="title">{{$utils->getArtistTitle($data['artist'])}}</h1>

    {!! $utils->getArtistDescription($data['artist']) !!}

    <img src="{{$data['artist']['image_large']}}">

    @foreach($data['albums'] as $album)
        <h3><a href="{{ $utils->getAlbumUrl($album) }}">{{ $album['name'] }}</a> - {{ $album['release_date'] }}</h3>

        <ul>
            @foreach($album['tracks'] as $track)
                <li><a href="{{ $utils->getTrackUrl($track)  }}">{{ $track['name'] }} - {{ $album['name'] }} - {{ $data['artist']['name'] }}</a></li>
            @endforeach
        </ul>
    @endforeach

    @if($data['artist']['similar'])
        <h2>Similar Artists</h2>

        @foreach($data['artist']['similar'] as $similarArtist)
            <h3><a href="{{ $utils->getArtistUrl($similarArtist) }}">{{ $similarArtist['name'] }}</a></h3>
        @endforeach
    @endif
</body>
</html>
