<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class QueueAnalysisService
{
    private $html;
    private $request;
    public $table;

    public function __construct($html)
    {
        $this->html = $html;
        $this->table = $this->initializeHtml($this->html);
    }

    private function initializeHtml($html)
    {
        $clean = str_replace(["\n", "\t", "\r", "  "], null, $html);
        $clean2 = str_replace(["&quot;"], null, $clean);
        $clean3 = preg_replace('~{(.*?)}~', null, $clean2);
        $clean4 = preg_replace('~{(.*?)}~', null, $clean3);
        preg_match('~<table class="table table-striped table-bordered datatable">(.*?)<\/table>~', $clean4, $table);
        preg_match('~<tbody>(.*?)</tbody>~', $table[1], $tbody);
        preg_match_all('~<tr>(.*?)</tr>~', $tbody[1], $rows);

        return $rows;
    }

    public function incoming()
    {
        $response = [];
        foreach ($this->table[1] as $key => $row) {
            preg_match_all('~<td .*?>(.*?)</td>~', $row, $columns);
            $counter = 0;
            foreach ($columns[1] as $column) {
                if ($counter == 0) {
                    $response[$key]["date"] = date('Y-m-d', strtotime(str_replace('/', '-', $column)));
                } else if ($counter == 1) {
                    $response[$key]["name"] = rtrim($column);
                } else if ($counter == 2) {
                    preg_match('~<span .*?>(.*?)</span>~', $column, $data);
                    $response[$key]["total_incoming_call"] = $data[1];
                } else if ($counter == 3) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["total_incoming_success_call"] = $data[1];
                } else if ($counter == 4) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $exploded = explode('/', $data[1]);
                    $response[$key]["total_incoming_error_call"] = trim($exploded[0]);
                } else if ($counter == 5) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["total_incoming_abandoned_call"] = $data[1];
                }

                $counter++;
            }
        }

        return $response;
    }

    public function outgoing()
    {
        $response = [];
        foreach ($this->table[1] as $key => $row) {
            preg_match_all('~<td .*?>(.*?)</td>~', $row, $columns);
            $counter = 0;
            foreach ($columns[1] as $column) {
                if ($counter == 0) {
                    $response[$key]["date"] = date('Y-m-d', strtotime(str_replace('/', '-', $column)));
                } else if ($counter == 1) {
                    $response[$key]["name"] = rtrim($column);
                } else if ($counter == 2) {
                    preg_match('~<span .*?>(.*?)</span>~', $column, $data);
                    $response[$key]["total_outgoing_call"] = $data[1];
                } else if ($counter == 3) {
                    preg_match_all('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["total_outgoing_success_call"] = $data[1][0];
                    $response[$key]["total_outgoing_error_call"] = $data[1][1];
                }

                $counter++;
            }
        }

        return $response;
    }

}
