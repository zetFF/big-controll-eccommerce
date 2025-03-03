<?php

namespace App\Services;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class ElasticsearchService
{
    private $client;
    private $index;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([config('services.elasticsearch.host')])
            ->build();
        
        $this->index = config('services.elasticsearch.index');
    }

    public function index(Model $model, array $data): void
    {
        $params = [
            'index' => $this->index,
            'id' => $this->getDocumentId($model),
            'body' => array_merge($data, [
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'indexed_at' => now()->toIso8601String()
            ])
        ];

        $this->client->index($params);
    }

    public function search(string $query, array $filters = [], int $size = 20): Collection
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'multi_match' => [
                                'query' => $query,
                                'fields' => ['title^3', 'content^2', 'tags'],
                                'fuzziness' => 'AUTO'
                            ]
                        ]
                    ]
                ],
                'size' => $size
            ]
        ];

        if (!empty($filters)) {
            $params['body']['query']['bool']['filter'] = $this->buildFilters($filters);
        }

        $response = $this->client->search($params);
        return $this->formatResults($response);
    }

    public function delete(Model $model): void
    {
        $params = [
            'index' => $this->index,
            'id' => $this->getDocumentId($model)
        ];

        try {
            $this->client->delete($params);
        } catch (\Exception $e) {
            // Document might not exist
        }
    }

    public function bulk(Collection $models, callable $dataCallback): void
    {
        $params = ['body' => []];

        foreach ($models as $model) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->index,
                    '_id' => $this->getDocumentId($model)
                ]
            ];

            $params['body'][] = array_merge($dataCallback($model), [
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'indexed_at' => now()->toIso8601String()
            ]);
        }

        if (!empty($params['body'])) {
            $this->client->bulk($params);
        }
    }

    private function getDocumentId(Model $model): string
    {
        return sprintf(
            '%s_%s',
            str_replace('\\', '_', get_class($model)),
            $model->getKey()
        );
    }

    private function buildFilters(array $filters): array
    {
        $elasticFilters = [];

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $elasticFilters[] = ['terms' => [$field => $value]];
            } else {
                $elasticFilters[] = ['term' => [$field => $value]];
            }
        }

        return $elasticFilters;
    }

    private function formatResults(array $response): Collection
    {
        return collect($response['hits']['hits'])->map(function ($hit) {
            return array_merge(
                $hit['_source'],
                ['score' => $hit['_score']]
            );
        });
    }
} 