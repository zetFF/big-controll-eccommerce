<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ReportGenerationService
{
    public function generateReport(Report $report): string
    {
        $report->update(['status' => 'processing']);

        try {
            $data = $this->fetchReportData($report);
            $filePath = $this->createReport($report, $data);

            $report->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'last_generated_at' => now()
            ]);

            return $filePath;
        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function fetchReportData(Report $report): array
    {
        return match ($report->type) {
            'sales' => $this->getSalesReport($report->parameters),
            'inventory' => $this->getInventoryReport($report->parameters),
            'users' => $this->getUsersReport($report->parameters),
            'orders' => $this->getOrdersReport($report->parameters),
            'custom' => $this->getCustomReport($report->parameters),
            default => throw new \Exception('Invalid report type'),
        };
    }

    private function getSalesReport(array $parameters): array
    {
        $query = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->whereBetween('created_at', [
                Carbon::parse($parameters['start_date']),
                Carbon::parse($parameters['end_date'])
            ])
            ->groupBy('date')
            ->orderBy('date');

        return [
            'headers' => ['Date', 'Total Orders', 'Total Sales'],
            'data' => $query->get()->toArray()
        ];
    }

    private function getInventoryReport(array $parameters): array
    {
        $query = DB::table('products')
            ->select(
                'name',
                'stock',
                'price',
                DB::raw('stock * price as stock_value')
            )
            ->when(isset($parameters['min_stock']), function ($q) use ($parameters) {
                return $q->where('stock', '<=', $parameters['min_stock']);
            });

        return [
            'headers' => ['Product', 'Stock', 'Price', 'Stock Value'],
            'data' => $query->get()->toArray()
        ];
    }

    private function createReport(Report $report, array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        foreach ($data['headers'] as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        // Add data
        foreach ($data['data'] as $row => $rowData) {
            foreach ((array)$rowData as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col + 1, $row + 2, $value);
            }
        }

        $fileName = sprintf(
            'reports/%s_%s_%s.xlsx',
            $report->type,
            $report->id,
            now()->format('Y-m-d_H-i-s')
        );

        $writer = new Xlsx($spreadsheet);
        $writer->save(Storage::path($fileName));

        return $fileName;
    }
} 