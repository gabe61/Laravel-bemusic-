<?php namespace App\Http\Controllers;

use Common\Auth\Roles\Role;
use DB;
use Auth;
use Cache;
use Artisan;
use Exception;
use Common\Settings\Setting;
use Common\Core\Controller;
use Common\Settings\DotEnvEditor;
use Illuminate\Support\Collection;

class UpdateController extends Controller {
    /**
     * @var DotEnvEditor
     */
    private $dotEnvEditor;

    /**
     * @var Setting
     */
    private $setting;

    /**
     * UpdateController constructor.
     *
     * @param DotEnvEditor $dotEnvEditor
     * @param Setting $setting
     */
	public function __construct(DotEnvEditor $dotEnvEditor, Setting $setting)
	{
        $this->setting = $setting;
        $this->dotEnvEditor = $dotEnvEditor;

	    if ( ! config('common.site.disable_update_auth') && version_compare(config('common.site.version'), $this->getAppVersion()) === 0) {
            if ( ! Auth::check() || ! Auth::user()->hasPermission('admin')) {
                abort(403);
            }
        }
    }

    /**
     * Show update view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('update');
    }

    /**
     * Perform the update.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
	{
        //fix "index is too long" issue on MariaDB and older mysql versions
        \Schema::defaultStringLength(191);

        Artisan::call('migrate', ['--force' => 'true']);
        Artisan::call('db:seed', ['--force' => 'true']);
        Artisan::call('common:seed');

        //set albums table collation and charset to utf8mb4
        try {
            $tables = DB::select('SHOW TABLES');
            $prefix = DB::getTablePrefix();

            foreach($tables as $table) {
                $name = head($table);
                DB::statement("ALTER TABLE {$prefix}{$name} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            }
        } catch(Exception $e) {
            //
        }

        if (version_compare(config('common.site.version'), '2.3.0') === -1) {
            // update date format
            $this->setting->where('name', 'dates.format')->update(['value' => 'yyyy-MM-dd']);

            //rename "uploads" permission to "files"
            Role::orderBy('id')->chunk(50, function(Collection $roles) {
                $roles->each(function(Role $role) {
                    $oldPermissions = json_encode($role->permissions);
                    $newPermissions = str_replace('uploads', 'files', $oldPermissions);
                    $role->permissions = $newPermissions;
                    $role->save();
                });
            });
        }

        //move translations from database to filesystem
        if (version_compare(config('common.site.version'), '2.2.0') === -1) {
            Artisan::call('translations:migrate_from_database');
            Artisan::call('custom_code:migrate_from_database');
            $this->setting->where('name', 'dates.format')->update(['value' => 'yyyy-MM-dd']);
        }

        //versions earlier then 2.1.8 were using symlinks by default
        if (version_compare(config('common.site.version'), '2.1.8') === -1) {
            $this->dotEnvEditor->write(['USE_SYMLINKS' => true]);
            Artisan::call('storage:link');
        }

        //radio provider should always be spotify
        $this->setting->where('name', 'radio_provider')->update(['value' => 'Spotify']);

        $version = $this->getAppVersion();
        $this->dotEnvEditor->write(['app_version' => $version]);

        Cache::flush();

        return redirect('/')->with('status', 'Updated the site successfully.');
	}


    /**
     * Get new app version.
     *
     * @return string
     */
    private function getAppVersion()
    {
        try {
            return $this->dotEnvEditor->load(base_path('.env.example'))['app_version'];
        } catch (Exception $e) {
            return '2.3.1';
        }
    }
}