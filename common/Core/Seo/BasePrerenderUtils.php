<?php namespace Common\Core\Seo;

use Illuminate\Http\Request;
use Common\Settings\Settings;

class BasePrerenderUtils
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    /**
     * Get site name setting.
     *
     * @return string
     */
    public function getSiteName()
    {
        return $this->settings->get('branding.site_name');
    }

    /**
     * Get specified page seo title.
     *
     * @param string $name
     * @param string $find
     * @param string $replace
     * @return string
     */
    public function getTitle($name, $find = null, $replace = null)
    {
        $title = $this->settings->get("seo.{$name}_title");

        if ($find && $replace) {
            $title = $this->replacePlaceholder($find, $replace, $title);
        }

        return $title;
    }

    /**
     * Get specified page seo description.
     *
     * @param string $name
     * @param string $find
     * @param string $replace
     * @return string
     */
    public function getDescription($name, $find = null, $replace = null)
    {
        $description = $this->settings->get("seo.{$name}_description");

        if ($find && $replace) {
           $description =  $this->replacePlaceholder($find, $replace, $description);
        }

        return $description;
    }

    /**
     * Get homepage seo title.
     *
     * @return string
     */
    public function getHomeTitle()
    {
        return $this->settings->get("seo.home_title");
    }

    /**
     * Get homepage seo description.
     *
     * @return string
     */
    public function getHomeDescription()
    {
        return $this->settings->get("seo.home_description");
    }

    /**
     * Get absolute url for homepage.
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return url('');
    }

    /**
     * Replace placeholder with actual value in specified string.
     *
     * @param string $key
     * @param string $value
     * @param string $subject
     * @return string
     */
    protected function replacePlaceholder($key, $value, $subject) {
        return str_replace('{{'.$key.'}}', $value, $subject);
    }
}