<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use League\Csv\Reader;
use Illuminate\Support\Collection;

class DataExportService
{
    public function exportToCsv(Collection $data, array $headers, string $filename): string
    {
        $csv = Writer::createFromString('');
        $csv->insertOne($headers);

        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                $csvRow[] = data_get($row, $header, '');
            }
            $csv->insertOne($csvRow);
        }

        $path = "exports/{$filename}";
        Storage::put($path, $csv->toString());

        return $path;
    }

    public function exportToJson(Collection $data, string $filename): string
    {
        $path = "exports/{$filename}";
        Storage::put($path, $data->toJson(JSON_PRETTY_PRINT));

        return $path;
    }

    public function exportToExcel(Collection $data, array $headers, string $filename): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        foreach ($headers as $colNum => $header) {
            $sheet->setCellValueByColumnAndRow($colNum + 1, 1, $header);
        }

        // Add data
        $row = 2;
        foreach ($data as $item) {
            $col = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col, $row, data_get($item, $header, ''));
                $col++;
            }
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $path = "exports/{$filename}";
        $writer->save(Storage::path($path));

        return $path;
    }
} 