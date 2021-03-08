<?php

namespace App\Services;


class EmployeeCallAnalysisService
{
    public $html;
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
        preg_match('~<table class="table table-striped table-bordered datatable" id="datatable">(.*?)<\/table>~', $clean4, $table);
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
                    $response[$key]["date"] = $column;
                } else if ($counter == 1) {
                    preg_match('~<span .*?>(.*?)</span>~', $column, $data);
                    $exploded = explode(' - ', $data[1]);
                    $response[$key]["extension"] = trim($exploded[0]);
                    $response[$key]["name"] = rtrim($exploded[1]);
                } else if ($counter == 2) {
                    $response[$key]["total_success_call"] = $column;
                } else if ($counter == 3) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["incoming_success_call"] = $data[1];
                } else if ($counter == 4) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["outgoing_success_call"] = $data[1];
                } else if ($counter == 9) {
                    $response[$key]["outgoing_total_call_time"] = $column;
                } else if ($counter == 10) {
                    $response[$key]["incoming_total_call_time"] = $column;
                } else if ($counter == 11) {
                    $response[$key]["total_ring_time"] = $column;
                } else if ($counter == 12) {
                    $response[$key]["total_wait_time"] = $column;
                } else if ($counter == 16) {
                    $response[$key]["total_call_time"] = $column;
                } else if ($counter == 26) {
                    $response[$key]["operational_productivity_rate"] = preg_replace('/[^0-9]/', '', $column);
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
                    $response[$key]["date"] = $column;
                } else if ($counter == 1) {
                    preg_match('~<span .*?>(.*?)</span>~', $column, $data);
                    $exploded = explode(' - ', $data[1]);
                    $response[$key]["extension"] = trim($exploded[0]);
                    $response[$key]["name"] = rtrim($exploded[1]);
                } else if ($counter == 2) {
                    $response[$key]["outgoing_success_call"] = $column;
                } else if ($counter == 3) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["outgoing_success_call"] = $data[1];
                } else if ($counter == 4) {
                    preg_match('~<button .*?>(.*?)</button>~', $column, $data);
                    $response[$key]["outgoing_error_call"] = $data[1];
                }
                $counter++;
            }
        }

        return $response;
    }

}
