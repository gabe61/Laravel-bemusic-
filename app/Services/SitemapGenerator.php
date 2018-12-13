<?php namespace App\Services;

use DB;
use App;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Storage;
use Carbon\Carbon;
use Common\Settings\Settings;

class SitemapGenerator {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * How much records to process per db query.
     *
     * @var integer
     */
    private $queryLimit = 6000;

    /**
     * Base site url.
     *
     * @var string
     */
    private $baseUrl = '';

    /**
     * Storage directory url.
     *
     * @var string
     */
    private $storageUrl = '';

    /**
     * Current date and time string.
     *
     * @var string
     */
    private $currentDateTimeString = '';

    /**
     * Resources to generate sitemap for and columns needed.
     *
     * @var array
     */
    private $resources = [
        'albums'     => ['id', 'name', 'artist_id'],
        'playlists'  => ['id', 'name'],
        'artists'    => ['id', 'name', 'updated_at'],
        'tracks'     => ['id', 'name'],
        'genres'     => ['id', 'name', 'updated_at'],
    ];

    /**
     * How many items do we have in current xml string.
     *
     * @var int
     */
    private $counter = 0;

    /**
     * How many sitemaps we have already generated for current resource.
     *
     * @var int
     */
    private $sitemapCounter = 1;

    /**
     * Xml sitemap string.
     *
     * @var string|boolean
     */
    private $xml = false;

    /**
     * Create new SitemapController instance.
     *
     * @param Settings $settings
     * @param Filesystem $fs
     */
    public function __construct(Settings $settings, Filesystem $fs)
    {
        $this->fs = $fs;
        $this->settings = $settings;
        $this->baseUrl = url('') . '/';
        $this->storageUrl = url('storage') . '/';
        $this->currentDateTimeString = Carbon::now()->toDateTimeString();

        ini_set('memory_limit', '160M');
        ini_set('max_execution_time', 7200);
    }

    /**
     * Generate a sitemap of all urls of the site.
     *
     * @return void
     */
    public function generate()
    {
        $index = [];

        foreach ($this->resources as $name => $columns) {
            $index[$name] = $this->makeDynamicMaps($name, $columns);
        }

        $this->makeStaticMap();
        $this->makeIndex($index);
    }

    /**
     * Make fully qualified url on the site for given item.
     *
     * @param string $name
     * @param object $item
     * @return string
     */
    private function makeItemUrl($name, $item)
    {
        if ($name === 'artists') {
            return $this->baseUrl.'artist/'.$this->encodeUrlParam($item->name);
        }

        if ($name === 'albums') {
            if (isset($item->artist) && $item->artist) {
                return $this->baseUrl.'album/'.$item->id.'/'.$this->encodeUrlParam($item->artist->name).'/'.$this->encodeUrlParam($item->name);
            } else {
                return $this->baseUrl.'album/'.$item->id.'/'.$this->encodeUrlParam($item->name);
            }
        }

        if ($name === 'tracks') {
            return $this->baseUrl.'track/'.$item->id;
        }

        if ($name === 'playlists') {
            return $this->baseUrl.'playlist/'.$item->id;
        }

        if ($name === 'genres') {
            return $this->baseUrl.'genre/'.$this->encodeUrlParam($item->name);
        }
    }

    /**
     * Encode given string so it's accepted by the url.
     *
     * @param string $string
     * @return string
     */
    private function encodeUrlParam($string)
    {
        $string = str_replace('+', '%2B', $string);
        $string = str_replace(' ', '+', $string);
        $string = str_replace('#', '%23', $string);
        $string = str_replace('/', '%252F', $string);

        return strtolower($string);
    }

    private function getItemUpdatedAtTime($item = null)
    {
        $date = (! isset($item->updated_at) || $item->updated_at == '0000-00-00 00:00:00') ? $this->currentDateTimeString : $item->updated_at;
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    /**
     * Generate sitemap and save it to a file.
     *
     * @param string $fileName
     */
    private function save($fileName)
    {
        $this->xml .= "\n</urlset>";

        Storage::disk('public')->put('sitemaps/'.$fileName.'.xml', $this->xml);

        $this->xml = false;
        $this->counter = 0;
        $this->sitemapCounter++;
    }

    private function getModel($name, $columns)
    {
        if ($name === 'artists') {
            return DB::table('artists')->where('fully_scraped', 1)->select($columns);
        } else if ($name === 'albums') {
            return DB::table('albums')->where('fully_scraped', 1)->select($columns);
        } else if ($name === 'tracks') {
            return DB::table('tracks')->select($columns);
        } else if ($name === 'playlists') {
            return DB::table('playlists')->where('public', 1)->select($columns);
        } else if ($name === 'genres') {
            return DB::table('genres')->select($columns);
        }
    }

    /**
     * Add new url line to xml string.
     *
     * @param string $name
     * @param mixed $item
     * @param string $url
     * @param string $updatedAt
     */
    private function addNewLine($name = null, $item = null, $url = null, $updatedAt = null)
    {
        $url       = $url ? $url : $this->makeItemUrl($name, $item);
        $updatedAt = $updatedAt ? $updatedAt : $this->getItemUpdatedAtTime($item);

        if ($this->xml === false) {
            $this->startNewXmlFile();
        }

        if ($this->counter === 50000) {
            $this->save("$name-sitemap-{$this->sitemapCounter}");
            $this->startNewXmlFile();
        }

        $line = "\t"."<url>\n\t\t<loc>".htmlspecialchars($url)."</loc>\n\t\t<lastmod>".$updatedAt."</lastmod>\n\t\t<changefreq>weekly</changefreq>\n\t\t<priority>1.00</priority>\n\t</url>\n";

        $this->xml .= $line;

        $this->counter++;
    }

    /**
     * Add xml headers to xml string
     */
    private function startNewXmlFile()
    {
        $this->xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    }

    /**
     * Create sitemaps for all dynamic resources.
     *
     * @param  string $name
     * @param  array $columns
     * @return integer
     */
    private function makeDynamicMaps($name, array $columns)
    {
        $this->getModel($name, $columns)->orderBy('id')->chunk($this->queryLimit, function($items) use($name)
        {
            if ($name === 'albums') {
                $items = $this->getAlbumsArtists($items);
            }

            foreach ($items as $item) {
                $this->addNewLine($name, $item);
            }
        });

        //check for unused items
        if ($this->xml) {
            $this->save("$name-sitemap-{$this->sitemapCounter}");
        }

        $index = $this->sitemapCounter-1;

        $this->sitemapCounter = 1;
        $this->counter = 0;

        return $index;
    }

    private function getAlbumsArtists($albums)
    {
        $ids = [];

        foreach($albums as $album) {
            if ( ! isset($ids[$album->artist_id])) {
                $ids[$album->artist_id] = ['id' => $album->artist_id];
            }
        }

        DB::table('sitemap_ids')->truncate();

        //insert artist ids we need into temp table
        DB::table('sitemap_ids')->insert($ids);

        //fetch artists via inner join
        $artists = DB::table('artists')->join('sitemap_ids', 'artists.id', '=', 'sitemap_ids.id')->select(['artists.id', 'name'])->get();

        $newAlbums = [];

        //attach artist to albums
        foreach($albums as $k => $album) {
            foreach($artists as $artist) {
                if ($artist->id === $album->artist_id) {
                    $album->artist = $artist;
                    $newAlbums[] = $album;
                    continue;
                }
            }
        }

        return $newAlbums;
    }

    /**
     * Create a sitemap for static pages.
     *
     * @return void
     */
    private function makeStaticMap()
    {
        $this->addNewLine(false, false, $this->baseUrl, $this->getItemUpdatedAtTime());
        $this->addNewLine(false, false, $this->baseUrl.'new-releases', $this->getItemUpdatedAtTime());
        $this->addNewLine(false, false, $this->baseUrl.'popular-genres', $this->getItemUpdatedAtTime());
        $this->addNewLine(false, false, $this->baseUrl.'top-songs', $this->getItemUpdatedAtTime());
        $this->addNewLine(false, false, $this->baseUrl.'popular-albums', $this->getItemUpdatedAtTime());

        DB::table('pages')->get()->each(function($page) {
            $slug = Str::slug($page->slug);
            $this->addNewLine(false, false, $this->baseUrl."pages/$page->id/$slug", $this->getItemUpdatedAtTime());
        });

        $this->save("static-urls-sitemap");
    }

    /**
     * Create a sitemap index from all individual sitemaps.
     *
     * @param  array  $index
     * @return void
     */
    private function makeIndex(array $index)
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($index as $resource => $number) {
            for ($i=1; $i <= $number; $i++) {
                $url = $this->storageUrl."sitemaps/{$resource}-sitemap-$i.xml";
                $string .= "\t<sitemap>\n"."\t\t<loc>$url</loc>\n"."\t\t<lastmod>{$this->getItemUpdatedAtTime()}</lastmod>\n"."\t</sitemap>\n";
            }
        }

        $string .= "\t<sitemap>\n\t\t<loc>{$this->storageUrl}/sitemaps/static-urls-sitemap.xml</loc>\n\t\t<lastmod>{$this->getItemUpdatedAtTime()}</lastmod>\n\t</sitemap>\n";

        $string .= '</sitemapindex>';

        Storage::disk('public')->put('sitemaps/sitemap-index.xml', $string);
    }
}
