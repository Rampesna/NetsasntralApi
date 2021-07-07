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

    public static function Abandons(Request $request)
    {
        $params = [
            "draw" => "1",
            "columns[0][data]" => "callerNumber",
            "columns[1][data]" => "standByTime",
            "columns[2][data]" => "callTime",
            "columns[3][data]" => "status",
            "columns[4][data]" => "result",
            "columns[5][data]" => "callbackTime",
            "columns[6][data]" => "queue",
            "columns[7][data]" => "callbackAgent",
            "order[0][column]" => "2",
            "order[0][dir]" => "desc",
            "start" => "0",
            "length" => "100",
            "_csrf_token" => "istatistik_detay_getir",
            "t1" => date('d.m.Y'),
            "t2" => date('d.m.Y'),
            "departman" => "EfaturaEarsiv",
            "tip" => "abandon",
            "startHour" => "",
            "endHour" => "",
            "indate" => "",
            "maxbekleme" => ""
        ];

        foreach ($request->queues ?? [] as $queue) {
            $params['departman'] = $queue;
            $response[$queue] = Http::asForm()->post('http://uyumsoft.netasistan.com/istatistik/departman/detay', $params)["data"];
        }

        return response()->json($response);
    }
}
