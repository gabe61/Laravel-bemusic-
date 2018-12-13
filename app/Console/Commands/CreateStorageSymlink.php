<?php

namespace App\Console\Commands;

use Artisan;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateStorageSymlink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:symlink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create symlink for storage public directory.';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * Create a new command instance.
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        parent::__construct();
        $this->fs = $fs;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->createTestFile();

        if ($this->tryDefaultSymlink() || $this->tryAlternativeSymlink()) {
            $this->info('Created storage symlink successfully.');
            return;
        }

        //reset default symlink
        $this->tryDefaultSymlink();

        $this->warn('Created storage symlink, but it does not seem to be working properly.');
    }

    /**
     * Create a file for testing storage symlink.
     */
    private function createTestFile()
    {
        $path = storage_path('app/public/symlink_test.txt');

        if ( ! $this->fs->exists($path)) {
            $this->fs->put($path, 'works');
        }
    }

    /**
     * Try alternative symlink creation.
     *
     * @return bool
     */
    private function tryAlternativeSymlink()
    {
        $this->removeCurrentSymlink();
        //symlink('../storage/app/public', './storage');
        symlink('../../../storage/app/public', '../../../public/storage');

        return $this->symlinkWorks();
    }

    /**
     * Try default laravel storage symlink.
     *
     * @return bool
     */
    private function tryDefaultSymlink()
    {
        $this->removeCurrentSymlink();
        Artisan::call('storage:link');

        return $this->symlinkWorks();
    }

    /**
     * Check if current storage symlink works properly.
     *
     * @return bool
     */
    private function symlinkWorks()
    {
        $http = new Client(['verify' => false, 'exceptions' => false]);
        $response = $http->get(url('storage/symlink_test.txt'))->getBody()->getContents();
        return $response === 'works';
    }

    /**
     * Remove current storage symlink.
     *
     * @return bool
     */
    private function removeCurrentSymlink()
    {
        try {
            return unlink(public_path('storage'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
