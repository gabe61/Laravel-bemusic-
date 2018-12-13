<?php

namespace App\Console;

use App\Console\Commands\CachePaginationCounts;
use App\Console\Commands\DeleteAlbumsWithoutArtists;
use App\Console\Commands\DeleteTracksWithoutAlbum;
use App\Console\Commands\MigrateDatabaseCustomCode;
use App\Console\Commands\MigrateDatabaseTranslations;
use App\Console\Commands\ResetDemoAdminAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CachePaginationCounts::class,
        DeleteAlbumsWithoutArtists::class,
        DeleteTracksWithoutAlbum::class,
        ResetDemoAdminAccount::class,
        MigrateDatabaseTranslations::class,
        MigrateDatabaseCustomCode::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('pagination:cache')->daily();

        if (config('common.site.demo')) {
            $schedule->command(ResetDemoAdminAccount::class)->daily();
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
