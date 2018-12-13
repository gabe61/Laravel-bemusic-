<?php namespace App\Services\Providers;

use App;
use Common\Settings\Settings;

class ProviderResolver
{
    /**
     * Settings service instance.
     *
     * @var Settings
     */
    private $settings;

    /**
     * Default data type to data provider map.
     *
     * @var array
     */
    private $defaults = [
        'artist' => 'local',
        'album' => 'local',
        'search' => 'local',
        'audio_search' => 'youtube',
        'new_releases' => 'local',
        'top_albums' => 'local',
        'top_tracks' => 'local',
        'genres' => 'local',
        'radio' => 'spotify'
    ];

    /**
     * Create new ProviderResolver instance.
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Resolve correct data provider for given data type.
     *
     * @param string $type
     * @return mixed
     */
    public function get($type)
    {
        $provider = $this->getProviderNameFor($type);

        // make fully qualified provider class name
        $namespace = $this->getNamespace($type, $provider);

        if ( ! $type || ! class_exists($namespace)) {
            $namespace = $this->getNamespace($type, $this->defaults[$type]);
        }

        return App::make($namespace);
    }

    /**
     * Get user specified or default provider for given data type.
     *
     * @param $type
     * @return string
     */
    public function getProviderNameFor($type)
    {
        return $this->settings->get($type . '_provider', $this->defaults[$type]);
    }

    /**
     * Make fully qualified namespace for provider class.
     *
     * @param string $type
     * @param string $provider
     * @return null|string
     */
    private function getNamespace($type, $provider)
    {
        if ( ! $type || ! $provider) return null;

        $type = ucfirst(camel_case($type));
        $provider = ucfirst(camel_case($provider));
        return 'App\Services\Providers\\' . $provider . '\\' . $provider . $type;
    }
}