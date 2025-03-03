<?php

namespace App\Traits;

use App\Models\SearchIndex;

trait Searchable
{
    public static function bootSearchable()
    {
        static::created(function ($model) {
            $model->updateSearchIndex();
        });

        static::updated(function ($model) {
            $model->updateSearchIndex();
        });

        static::deleted(function ($model) {
            $model->deleteSearchIndex();
        });
    }

    public function searchIndex()
    {
        return $this->morphOne(SearchIndex::class, 'searchable');
    }

    public function updateSearchIndex()
    {
        $searchData = $this->toSearchableArray();

        $this->searchIndex()->updateOrCreate(
            ['searchable_type' => get_class($this), 'searchable_id' => $this->id],
            [
                'title' => $searchData['title'],
                'content' => $searchData['content'],
                'type' => $searchData['type'],
                'metadata' => $searchData['metadata'] ?? [],
                'permissions' => $searchData['permissions'] ?? []
            ]
        );
    }

    public function deleteSearchIndex()
    {
        $this->searchIndex()->delete();
    }

    abstract public function toSearchableArray(): array;
} 