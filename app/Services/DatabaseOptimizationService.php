<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseOptimizationService
{
    public function optimizeTables(): array
    {
        $results = [];
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            try {
                DB::statement("ANALYZE TABLE {$table}");
                DB::statement("OPTIMIZE TABLE {$table}");
                $results[$table] = 'optimized';
            } catch (\Exception $e) {
                Log::error("Failed to optimize table {$table}: " . $e->getMessage());
                $results[$table] = 'failed';
            }
        }

        return $results;
    }

    public function analyzeQueryPerformance(): array
    {
        DB::enableQueryLog();

        // Run your queries here
        $slowQueries = DB::getQueryLog();

        $analysis = collect($slowQueries)
            ->map(function ($query) {
                return [
                    'query' => $query['query'],
                    'time' => $query['time'],
                    'suggestions' => $this->analyzeSingleQuery($query['query'])
                ];
            })
            ->sortByDesc('time')
            ->values()
            ->all();

        DB::disableQueryLog();

        return $analysis;
    }

    private function analyzeSingleQuery(string $query): array
    {
        $suggestions = [];

        // Check for SELECT *
        if (stripos($query, 'select *') !== false) {
            $suggestions[] = 'Consider selecting specific columns instead of SELECT *';
        }

        // Check for missing indexes
        if (stripos($query, 'where') !== false && stripos($query, 'using index') === false) {
            $suggestions[] = 'Consider adding appropriate indexes for WHERE clauses';
        }

        return $suggestions;
    }
} 