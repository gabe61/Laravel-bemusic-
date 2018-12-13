<?php namespace Common\Core\Controllers;

use Illuminate\Support\Str;
use Common\Core\Controller;
use Illuminate\Filesystem\Filesystem;
use Common\Localizations\Localization;

class ValueListsController extends Controller
{
    /**
     * Laravel filesystem service instance.
     *
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * ValueListsController constructor.
     *
     * @param Filesystem $fs
     * @param Localization $localization
     */
    public function __construct(Filesystem $fs, Localization $localization)
    {
        $this->fs = $fs;
        $this->localization = $localization;
    }

    /**
     * Get value list by specified name.
     *
     * @param string $name
     * @return mixed
     */
    public function getValueList($name)
    {
        $name = Str::studly($name);

        if ( ! method_exists($this, $name)) abort(404);

        return $this->$name();
    }

    /**
     * Get all available permissions.
     *
     * @return array
     */
    public function permissions()
    {
        $this->authorize('index', 'PermissionPolicy');

        $permissions = config('common.permissions.all');

        // format legacy permissions into ['name' => 'permission] array
        foreach ($permissions as $groupName => $group) {
            $permissions[$groupName] = array_map(function($permission) {
                if (is_array($permission)) return $permission;
                return ['name' => $permission];
            }, $group);
        }

        return $this->success(['permissions' => $permissions]);
    }

    /**
     * Get a list of currencies.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function currencies()
    {
        return $this->success([
            'currencies' => json_decode($this->fs->get(__DIR__ . '/../../resources/lists/currencies.json'), true)
        ]);
    }

    /**
     * Get timezones, countries and languages lists.
     *
     * @return array
     */
    public function selects()
    {
        $timezones = json_decode($this->fs->get(__DIR__ . '/../../resources/lists/timezones.json'), true);
        $countries = json_decode($this->fs->get(__DIR__ . '/../../resources/lists/countries.json'), true);
        $languages = $this->localization->get(['name'])->pluck('name')->toArray();

        return $this->success([
            'timezones' => array_values($timezones),
            'countries' => array_values($countries),
            'languages' => $languages,
        ]);
    }
}
