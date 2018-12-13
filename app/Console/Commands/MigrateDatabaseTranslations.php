<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Common\Localizations\Localization;
use Common\Localizations\LocalizationsRepository;

class MigrateDatabaseTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:migrate_from_database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy localization from database to filesystem.';

    /**
     * @var LocalizationsRepository
     */
    private $localizations;

    /**
     * Create a new command instance.
     *
     * @param LocalizationsRepository $localizations
     */
    public function __construct(LocalizationsRepository $localizations)
    {
        parent::__construct();
        $this->localizations = $localizations;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = DB::table('localizations')->pluck('id');

        $ids->map(function($id) {
            return Localization::find($id);
        })->each(function(Localization $localization) {
            $path = $this->localizations->makeLocalizationLinesPath($localization);
            if (file_exists($path)) return;
            $this->localizations->update($localization->id, ['lines' => $localization->lines]);
        });

        $this->info('Migrated legacy localizations');
    }
}
