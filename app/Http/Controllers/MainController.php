<?php

namespace App\Http\Controllers;

use App\Helpers\General;
use App\Services\EmployeeCallAnalysisService;
use App\Services\QueueAnalysisService;
use App\Services\SantralAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MainController extends Controller
{
    public function CallQueues()
    {
        return Http::get('http://uyumsoft.netasistan.com/home/dashboard/datarefresh/all', []);
    }

    public function EmployeeCallAnalysis(Request $request)
    {
        $dates = General::displayDates($request->start_date, $request->end_date);

        $analysis = [];

        foreach ($dates as $date) {
            $params = [
                'extensionName' => $request->extensions,
                'startDate' => $date,
                'endDate' => $date,
                'timeName' => 'custom'
            ];

            loginAgain:
            try {
                $incomingService = new EmployeeCallAnalysisService(Http::withHeaders([
                    'Cookie' => 'PHPSESSID=' . session('MY_PHPSESSID')
                ])->asForm()->post('http://uyumsoft.netasistan.com/istatistik/dahilibazli/adetpro', $params));

                $analysis['incoming'] = $incomingService->incoming();

                $outgoingService = new EmployeeCallAnalysisService(Http::withHeaders([
                    'Cookie' => 'PHPSESSID=' . session('MY_PHPSESSID')
                ])->asForm()->post('http://uyumsoft.netasistan.com/istatistik/dahilibazligiden/adet', $params));

                $analysis['outgoing'] = $outgoingService->outgoing();
            } catch (\Exception $exception) {
                (new SantralAuthService)->login();
                goto loginAgain;
            }
        }

        return $analysis;
    }

    public function QueueAnalysis(Request $request)
    {
        $analysis = [];

        $params = [
            'queueName' => $request->queues,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'timeName' => 'custom'
        ];

        loginAgain:
        try {
            $incomingService = new QueueAnalysisService(Http::withHeaders([
                'Cookie' => 'PHPSESSID=' . session('MY_PHPSESSID')
            ])->asForm()->post('http://uyumsoft.netasistan.com/istatistik/departmanbazli/adet', $params));
            $analysis['incoming'] = $incomingService->incoming();

            $outgoingService = new QueueAnalysisService(Http::withHeaders([
                'Cookie' => 'PHPSESSID=' . session('MY_PHPSESSID')
            ])->asForm()->post('http://uyumsoft.netasistan.com/istatistik/departmanbazligiden/adet', $params));
            $analysis['outgoing'] = $outgoingService->outgoing();
        } catch (\Exception $exception) {
            (new SantralAuthService)->login();
            goto loginAgain;
        }

        return $analysis;
    }
}
