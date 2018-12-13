<?php namespace App\Jobs;

use App\Album;
use App\Artist;
use App\Playlist;
use App\Track;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Session\Store;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class IncrementModelViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $modelId;

    /**
     * Type of the model.
     *
     * @var string
     */
    private $type;

    /**
     * Create a new command instance.
     *
     * @param integer $modelId
     * @param string $type
     */
    public function __construct($modelId, $type)
    {
        $this->type = $type;
        $this->modelId = $modelId;
    }

    /**
     * Execute the console command.
     *
     * @param Store $session
     * @return mixed
     */
    public function handle(Store $session)
    {
        if ( ! $this->shouldIncrement($session)) return;

        $session->put("{$this->type}-views.$this->modelId", Carbon::now()->timestamp);

        $this->incrementViews();
    }

    /**
     * Check if model views should be incremented.
     *
     * @param Store $session
     * @return boolean
     */
    private function shouldIncrement(Store $session)
    {
        $views = $session->get("{$this->type}-views");

        //user has not viewed this article yet
        if ( ! $views || ! isset($views[$this->modelId])) return true;

        //see if user last viewed this article over 10 hours ago
        $time = Carbon::createFromTimestamp($views[$this->modelId]);

        return Carbon::now()->diffInHours($time) > 10;
    }

    /**
     * Increment views or plays of specified model.
     */
    private function incrementViews() {
        switch ($this->type) {
            case 'artist':
                return Artist::where('id', $this->modelId)->increment('views');
            case 'album':
                return Album::where('id', $this->modelId)->increment('views');
            case 'playlist':
                return Playlist::where('id', $this->modelId)->increment('views');
            case 'track':
                return Track::where('id', $this->modelId)->increment('plays');
        }
    }
}
