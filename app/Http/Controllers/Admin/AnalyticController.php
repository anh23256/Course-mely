<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class AnalyticController extends Controller
{
    use  LoggableTrait;
    public function index()
    {
        try {
            $analyticsVisitorAndPageViews = Analytics::fetchVisitorsAndPageViews(Period::days(7));

            $analyticsData = Analytics::get(
                Period::days(7),
                ['sessions'],
                ['country']
            );

            // dd($analyticsData, $analyticsVisitorAndPageViews);

            return view('analytics.index');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error','Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
