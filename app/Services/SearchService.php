<?php

namespace App\Services;

use App\Models\SearchIndex;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    public function search(
        string $query,
        array $filters = [],
        array $options = []
    ): LengthAwarePaginator {
        $search = SearchIndex::search($query);

        if (!empty($filters['type'])) {
            $search->where('type', $filters['type']);
        }

        if (!empty($filters['types'])) {
            $search->whereIn('type', $filters['types']);
        }

        if (!auth()->user()->is_admin) {
            $search->where('permissions', 'public')
                ->orWhere('permissions', 'contains', ['user_id' => auth()->id()]);
        }

        return $search->paginate(
            $options['per_page'] ?? 15,
            'page',
            $options['page'] ?? 1
        );
    }

    public function suggest(string $query, int $limit = 5): array
    {
        return SearchIndex::search($query)
            ->take($limit)
            ->get()
            ->map(function ($result) {
                return [
                    'title' => $result->title,
                    'type' => $result->type,
                    'url' => $this->generateUrl($result)
                ];
            })
            ->toArray();
    }

    public function reindex(): void
    {
        SearchIndex::removeAllFromSearch();
        
        // Reindex all searchable models
        $searchableModels = config('scout.searchable_models', []);
        
        foreach ($searchableModels as $model) {
            $model::chunk(100, function ($records) {
                foreach ($records as $record) {
                    $record->updateSearchIndex();
                }
            });
        }
    }

    private function generateUrl(SearchIndex $result): string
    {
        return match ($result->type) {
            'api_doc' => "/api-docs/{$result->searchable_id}",
            'webhook' => "/webhooks/{$result->searchable_id}",
            'file' => "/files/{$result->searchable_id}",
            'collection' => "/collections/{$result->searchable_id}",
            default => "/{$result->type}/{$result->searchable_id}"
        };
    }
} 