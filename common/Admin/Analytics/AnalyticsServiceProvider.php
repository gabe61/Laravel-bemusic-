<?php

namespace Common\Admin\Analytics;

use Common\Admin\Analytics\Actions\GetAnalyticsData;
use Common\Admin\Analytics\Actions\GetGoogleAnalyticsData;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GetAnalyticsData::class, function ($app) {
            if (config('common.site.demo')) {
                return new GetDemoAnalyticsData();
            } else {
                return $app->make(GetGoogleAnalyticsData::class);
            }
        });
    }
}