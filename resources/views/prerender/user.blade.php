<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>{{ $utils->getUserTitle($user) }}</title>

    <meta name="google" content="notranslate">
    <link rel="canonical" href="{{ $utils->getUserUrl($user) }}">

    <meta itemprop="name" content="{{ $utils->getUserTitle($user) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $utils->getUserTitle($user) }}">
    <meta name="twitter:url" content="{{ $utils->getUserUrl($user) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $utils->getUserTitle($user) }}">
    <meta property="og:url" content="{{ $utils->getUserUrl($user) }}">
    <meta property="og:site_name" content="{{ $utils->getSiteName() }}">

    <meta property="og:type" content="profile">

    <meta property="og:description" content="{{ $utils->getUserDescription($user) }}">
    <meta itemprop="description" content="{{ $utils->getUserDescription($user) }}">
    <meta property="description" content="{{ $utils->getUserDescription($user) }}">
    <meta name="twitter:description" content="{{ $utils->getUserDescription($user) }}">

    <meta itemprop="image" content="{{ $user['avatar'] }}">
    <meta property="og:image" content="{{ $user['avatar'] }}">
    <meta name="twitter:image" content="{{ $user['avatar'] }}">
    <meta property="og:image:width" content="200">
    <meta property="og:image:height" content="200">
</head>

<body>
    <h1 class="title">{{$utils->getUserTitle($user)}}</h1>

    {!! $utils->getUserDescription($user) !!}
    <br>

    <img src="{{$user['avatar']}}">

    <h2>Followers</h2>
    <ul class="followers">
        @foreach($user['followers'] as $user)
            <li><a href="{{ $utils->getUserUrl($user) }}">{{ $user['display_name'] }}</a></li>
        @endforeach
    </ul>

    <h2>Followed Users</h2>
    <ul class="followed_users">
        @foreach($user['followedUsers'] as $user)
            <li><a href="{{ $utils->getUserUrl($user) }}">{{ $user['display_name'] }}</a></li>
        @endforeach
    </ul>

    <h2>Playlists</h2>
    <ul class="playlists">
        @foreach($user['playlists'] as $playlist)
            <li>
                <figure>
                    <img src="{{ $utils->getPlaylistImage($playlist) }}">
                    <figcaption>
                        <a href="{{ $utils->getPlaylistUrl($playlist) }}">{{ $playlist['name'] }}</a>
                    </figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
</body>
</html>
