<?php

namespace App\Console\Commands;

use Common\Admin\Appearance\AppearanceSaver;
use Illuminate\Console\Command;
use Common\Settings\Settings;

class MigrateDatabaseCustomCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom_code:migrate_from_database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy custom js and css from database to filesystem.';

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var AppearanceSaver
     */
    private $appearanceSaver;

    /**
     * Create a new command instance.
     *
     * @param Settings $settings
     * @param AppearanceSaver $appearanceSaver
     */
    public function __construct(Settings $settings, AppearanceSaver $appearanceSaver)
    {
        parent::__construct();
        $this->settings = $settings;
        $this->appearanceSaver = $appearanceSaver;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $css = $this->settings->get('custom_code.css');
        if ($css) {
            $this->appearanceSaver->saveCustomCode('custom_code.css', $css);
        }

        $js = $this->settings->get('custom_code.js');
        if ($js) {
            $this->appearanceSaver->saveCustomCode('custom_code.js', $js);
        }

        \DB::table('settings')->where('name', 'custom_code.css')->orWhere('name', 'custom_code.js')->delete();

        $this->info('Migrated legacy custom code successfully');
    }
}
