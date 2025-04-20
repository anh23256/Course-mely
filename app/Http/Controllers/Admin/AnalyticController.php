<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class AnalyticController extends Controller
{
    use  LoggableTrait;
    public function index(Request $request)
    {
        try {
            $startDate = Carbon::parse($request->input('startDate', Carbon::now()->subDays(7)));
            $endDate = Carbon::parse($request->input('endDate', Carbon::now()));

            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $daysDifference = $start->diffInDays($end);
            $groupBy = ($daysDifference > 60) ? 'yearMonth' : ($daysDifference >= 14 ? 'yearWeek' : 'date');

            $analyticsVisitorAndPageViews = Analytics::fetchVisitorsAndPageViews(Period::create($start, $end));

            $analyticsData = Analytics::get(
                Period::create($start, $end),
                ['newUsers', 'totalUsers', 'sessions'],
                [$groupBy]
            );

            $topBrowsers = Analytics::fetchTopBrowsers(Period::create($start, $end), 4);

            $analyticsUserSession = Analytics::get(
                Period::create($start, $end),
                ['totalUsers', 'sessions']
            );

            $fetchMostVisitedPages = Analytics::fetchVisitorsAndPageViewsByDate(Period::create($start, $end), 7)
                ->sortByDesc('activeUsers');

            $analyticsCountriesSession = Analytics::fetchTopCountries(Period::create($start, $end));

            $userDevices = Analytics::get(
                Period::create($start, $end),
                ['sessions'],
                ['deviceCategory']
            );

            $analyticsEngagement = Analytics::get(
                Period::create($start, $end),
                ['bounceRate'],
                ['sessionSource']
            );

            $analyticsHourlyTraffic = Analytics::get(
                Period::create(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
                ['sessions'],
                ['dayOfWeek', 'hour'],
                7
            );

            if ($request->ajax()) {
                return response()->json([
                    'analyticsData' => $analyticsData,
                    'analyticsUserSession' => $analyticsUserSession,
                    'analyticsCountriesSession' => $analyticsCountriesSession,
                    'topBrowsers' => $topBrowsers,
                    'fetchMostVisitedPages' => $fetchMostVisitedPages,
                    'analyticsEngagement' => $analyticsEngagement,
                    'userDevices' => $userDevices,
                ]);
            }

            return view('analytics.index', compact([
                'analyticsData',
                'analyticsUserSession',
                'analyticsCountriesSession',
                'topBrowsers',
                'fetchMostVisitedPages',
                'analyticsEngagement',
                'userDevices',
                'analyticsHourlyTraffic'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
