<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\DataExportService;
use App\Services\DataImportService;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class DataController extends BaseController
{
    public function __construct(
        private DataExportService $exportService,
        private DataImportService $importService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:products,orders,users',
            'format' => 'required|in:csv,json,excel'
        ]);

        $data = $this->getData($request->type);
        $headers = $this->getHeaders($request->type);
        $filename = "{$request->type}-" . now()->format('Y-m-d-His');

        try {
            $path = match ($request->format) {
                'csv' => $this->exportService->exportToCsv($data, $headers, "{$filename}.csv"),
                'json' => $this->exportService->exportToJson($data, "{$filename}.json"),
                'excel' => $this->exportService->exportToExcel($data, $headers, "{$filename}.xlsx"),
            };

            return $this->successResponse([
                'download_url' => url(Storage::url($path))
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Export failed: ' . $e->getMessage(), 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'type' => 'required|in:products,orders,users',
        ]);

        $path = $request->file('file')->store('imports');
        $rules = $this->getValidationRules($request->type);
        $model = $this->getModelClass($request->type);

        try {
            $extension = $request->file('file')->getClientOriginalExtension();
            $result = match ($extension) {
                'csv' => $this->importService->importFromCsv($path, $rules, $model),
                'json' => $this->importService->importFromJson($path, $rules, $model),
                default => throw new \Exception('Unsupported file format'),
            };

            return $this->successResponse($result);
        } catch (\Exception $e) {
            return $this->errorResponse('Import failed: ' . $e->getMessage(), 500);
        } finally {
            Storage::delete($path);
        }
    }

    private function getData(string $type): Collection
    {
        return match ($type) {
            'products' => Product::all(),
            'orders' => Order::with('items')->get(),
            'users' => User::all(),
        };
    }

    private function getHeaders(string $type): array
    {
        return match ($type) {
            'products' => ['id', 'name', 'price', 'stock'],
            'orders' => ['id', 'order_number', 'total_amount', 'status'],
            'users' => ['id', 'name', 'email'],
        };
    }

    private function getValidationRules(string $type): array
    {
        return match ($type) {
            'products' => [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
            ],
            'users' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ],
            default => [],
        };
    }

    private function getModelClass(string $type): string
    {
        return match ($type) {
            'products' => Product::class,
            'orders' => Order::class,
            'users' => User::class,
        };
    }
} 