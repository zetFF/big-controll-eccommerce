<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use Illuminate\Support\Collection;

class DataImportService
{
    public function importFromCsv(string $path, array $rules, string $model): array
    {
        $csv = Reader::createFromPath(Storage::path($path), 'r');
        $csv->setHeaderOffset(0);

        $records = collect($csv->getRecords());
        return $this->processImport($records, $rules, $model);
    }

    public function importFromJson(string $path, array $rules, string $model): array
    {
        $json = json_decode(Storage::get($path), true);
        $records = collect($json);
        return $this->processImport($records, $rules, $model);
    }

    protected function processImport(Collection $records, array $rules, string $model): array
    {
        $successful = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($records as $index => $record) {
                $validator = Validator::make($record, $rules);

                if ($validator->fails()) {
                    $failed++;
                    $errors["row_{$index}"] = $validator->errors()->toArray();
                    continue;
                }

                $model::create($record);
                $successful++;
            }

            if ($failed === 0) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'total' => $records->count(),
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
} 