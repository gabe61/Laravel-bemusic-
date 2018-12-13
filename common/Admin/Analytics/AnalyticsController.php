<?php namespace Common\Admin\Analytics;

use Cache;
use Carbon\Carbon;
use Common\Core\Controller;
use Common\Admin\Analytics\Actions\GetAnalyticsData;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Exception;

class AnalyticsController extends Controller
{
    /**
     * @var GetAnalyticsData
     */
    private $getDataAction;

    /**
     * @var GetAnalyticsHeaderDataAction
     */
    private $getHeaderDataAction;

    /**
     * @param GetAnalyticsData $getDataAction
     * @param GetAnalyticsHeaderDataAction $getHeaderDataAction
     */
    public function __construct(
        GetAnalyticsData $getDataAction,
        GetAnalyticsHeaderDataAction $getHeaderDataAction
    )
    {
        $this->getDataAction = $getDataAction;
        $this->getHeaderDataAction = $getHeaderDataAction;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function stats()
    {
        $this->authorize('index', 'ReportPolicy');

        $data = Cache::remember('analytics.data', Carbon::now()->addDay(), function() {
            return [
                'mainData' => $this->getMainData(),
                'headerData' => $this->getHeaderDataAction->execute(),
            ];
        });

        return $this->success($data);
    }

    private function getMainData() {
        try {
            return $this->getDataAction->execute();
        } catch (Exception $e) {
            return [];
        }
    }
}
