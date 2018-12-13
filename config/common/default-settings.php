<?php

return [
    //homepage
    ['name' => 'homepage.type', 'value' => 'default'],
    ['name' => 'homepage.value', 'value' => 'popular-genres'],

    //branding
    ['name' => 'branding.site_name', 'value' => 'BeMusic'],

    //cache
    ['name' => 'cache.report_minutes', 'value' => 60],
    ['name' => 'cache.homepage_days', 'value' => 1],
    ['name' => 'automation.artist_interval', 'value' => 7],

    //providers
    ['name' => 'artist_provider', 'value' => 'Local'],
    ['name' => 'album_provider', 'value' => 'Local'],
    ['name' => 'radio_provider', 'value' => 'Spotify'],
    ['name' => 'genres_provider', 'value' => 'Local'],
    ['name' => 'album_images_provider', 'value' => 'real'],
    ['name' => 'artist_images_provider', 'value' => 'real'],
    ['name' => 'new_releases_provider', 'value' => 'Local'],
    ['name' => 'top_tracks_provider', 'value' => 'Local'],
    ['name' => 'top_albums_provider', 'value' => 'Local'],
    ['name' => 'search_provider', 'value' => 'Local'],
    ['name' => 'audio_search_provider', 'value' => 'Youtube'],
    ['name' => 'artist_bio_provider', 'value' => 'wikipedia'],

    //player
    ['name' => 'youtube.suggested_quality', 'value' => 'default'],
    ['name' => 'youtube.region_code', 'value' => 'US'],
    ['name' => 'player.default_volume', 'value' => 30],
    ['name' => 'player.hide_queue', 'value' => 0],
    ['name' => 'player.hide_video', 'value' => 0],
    ['name' => 'player.hide_video_button', 'value' => 0],
    ['name' => 'player.hide_lyrics', 'value' => 0],
    ['name' => 'player.mobile.auto_open_overlay', 'value' => 1],
    ['name' => 'player.enable_download', 'value' => 0],
    ['name' => 'player.sort_method', 'value' => 'external'],

    //other
    ['name' => 'https.enable_cert_verification', 'value' => 1],
    ['name' => 'site.force_https', 'value' => 0],

    //SEO
    ['name' => 'seo.artist_title', 'value' => 'Listen to {{ARTIST_NAME}}'],
    ['name' => 'seo.artist_description', 'value' => 'some stuff {{ARTIST_DESCRIPTION}}'],
    ['name' => 'seo.album_title', 'value' => '{{ALBUM_NAME}}'],
    ['name' => 'seo.album_description', 'value' => '{{ALBUM_NAME}} album by {{ARTIST_NAME}} on {{SITE_NAME}}'],
    ['name' => 'seo.track_title', 'value' => '{{TRACK_NAME}}'],
    ['name' => 'seo.track_description', 'value' => '{{TRACK_NAME}}, a song by {{ARTIST_NAME}} on {{SITE_NAME}}'],
    ['name' => 'seo.playlist_title', 'value' => '{{PLAYLIST_NAME}}, playlist by {{CREATOR_NAME}} on {{SITE_NAME}}'],
    ['name' => 'seo.playlist_description', 'value' => '{{PLAYLIST_DESCRIPTION}}'],
    ['name' => 'seo.user_profile_title', 'value' => '{{DISPLAY_NAME}}'],
    ['name' => 'seo.user_profile_description', 'value' => '{{DISPLAY_NAME}} profile on {{SITE_NAME}}'],
    ['name' => 'seo.search_title', 'value' => 'Search results for {{QUERY}}'],
    ['name' => 'seo.search_description', 'value' => 'Search results for {{QUERY}}'],
    ['name' => 'seo.genre_title', 'value' => '{{GENRE_NAME}}'],
    ['name' => 'seo.genre_description', 'value' => '{{GENRE_DESCRIPTION}}'],
    ['name' => 'seo.new_releases_title', 'value' => 'Latest releases'],
    ['name' => 'seo.new_releases_description', 'value' => 'Browse and listen to newest releases from popular artists.'],
    ['name' => 'seo.popular_genres_title', 'value' => 'Popular Genres'],
    ['name' => 'seo.popular_genres_description', 'value' => 'Browse popular genres to discover new music.'],
    ['name' => 'seo.popular_albums_title', 'value' => 'Popular Albums'],
    ['name' => 'seo.popular_albums_description', 'value' => 'Most popular albums from hottest artists today.'],
    ['name' => 'seo.top_50_title', 'value' => 'Top 50 Tracks'],
    ['name' => 'seo.top_50_description', 'value' => 'Global Top 50 chart of most popular songs.'],
    ['name' => 'seo.homepage_title', 'value' => 'BeMusic - Listen to music for free.'],
    ['name' => 'seo.homepage_description', 'value' => "Find and listen to millions of songs, albums and artists, all completely free on BeMusic."],
    ['name' => 'seo.homepage_tags', 'value' => "music, online, listen, streaming, play, digital, album, artist, playlist"],

    //menus
    ['name' => 'menus', 'value' => json_encode([
        ['name' => 'Primary', 'position' => 'sidebar-primary', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Popular Albums', 'action' => 'popular-albums', 'icon' => 'album'],
            ['type' => 'route', 'order' => 2, 'label' => 'Popular Genres', 'action' => 'popular-genres', 'icon' => 'local-offer'],
            ['type' => 'route', 'order' => 3, 'label' => 'Top 50', 'action' => 'top-50', 'icon' => 'trending-up'],
            ['type' => 'route', 'order' => 4, 'label' => 'New Releases', 'action' => 'new-releases', 'icon' => 'new-releases']
        ]],
        ['name' => 'Secondary ', 'position' => 'sidebar-secondary', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Songs', 'action' => '/library/songs', 'icon' => 'audiotrack'],
            ['type' => 'route', 'order' => 2, 'label' => 'Albums', 'action' => '/library/albums', 'icon' => 'album'],
            ['type' => 'route', 'order' => 3, 'label' => 'Artists', 'action' => '/library/artists', 'icon' => 'mic']
        ]],
        ['name' => 'Mobile ', 'position' => 'mobile-bottom', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Genres', 'action' => '/popular-genres', 'icon' => 'local-offer'],
            ['type' => 'route', 'order' => 2, 'label' => 'Top 50', 'action' => '/top-50', 'icon' => 'trending-up'],
            ['type' => 'route', 'order' => 3, 'label' => 'Search', 'action' => '/search', 'icon' => 'search'],
            ['type' => 'route', 'order' => 4, 'label' => 'Your Music', 'action' => '/library', 'icon' => 'library-music'],
            ['type' => 'route', 'order' => 4, 'label' => 'Account', 'action' => '/account/settings', 'icon' => 'person']
        ]]
    ])],
];
