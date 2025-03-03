<?php

namespace App\Services;

use App\Models\ApiDoc;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class ApiDocumentationService
{
    public function generateDocumentation(): array
    {
        $routes = Route::getRoutes();
        $paths = [];
        $schemas = [];

        foreach ($routes as $route) {
            if (!Str::startsWith($route->uri(), 'api/')) {
                continue;
            }

            $path = $this->generatePathItem($route);
            $paths['/' . $route->uri()] = $path;

            // Extract request/response schemas
            if ($route->controller) {
                $schemas = array_merge(
                    $schemas,
                    $this->extractSchemas($route->controller)
                );
            }
        }

        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => config('app.name') . ' API',
                'version' => '1.0.0',
                'description' => 'API documentation for ' . config('app.name'),
            ],
            'servers' => [
                ['url' => config('app.url') . '/api'],
            ],
            'paths' => $paths,
            'components' => [
                'schemas' => $schemas,
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
        ];
    }

    public function publishDocumentation(array $spec): ApiDoc
    {
        $existingDoc = ApiDoc::where('is_published', true)->first();
        
        if ($existingDoc) {
            $existingDoc->update(['is_published' => false]);
        }

        return ApiDoc::create([
            'version' => $spec['info']['version'],
            'title' => $spec['info']['title'],
            'description' => $spec['info']['description'],
            'spec' => $spec,
            'is_published' => true,
            'created_by' => auth()->id(),
            'published_at' => now(),
        ]);
    }

    private function generatePathItem($route): array
    {
        $methods = array_map('strtolower', $route->methods());
        $pathItem = [];

        foreach ($methods as $method) {
            if (in_array($method, ['head', 'options'])) {
                continue;
            }

            $operation = [
                'tags' => $this->generateTags($route),
                'summary' => $this->generateSummary($route),
                'parameters' => $this->generateParameters($route),
                'responses' => $this->generateResponses($route),
            ];

            if (in_array($method, ['post', 'put', 'patch'])) {
                $operation['requestBody'] = $this->generateRequestBody($route);
            }

            if ($route->middleware()) {
                $operation['security'] = [['bearerAuth' => []]];
            }

            $pathItem[$method] = $operation;
        }

        return $pathItem;
    }

    private function generateTags($route): array
    {
        $controller = $route->controller;
        if (!$controller) {
            return ['default'];
        }

        $className = class_basename($controller);
        return [str_replace('Controller', '', $className)];
    }

    private function generateSummary($route): string
    {
        $action = $route->getActionMethod();
        return Str::title(str_replace('_', ' ', $action));
    }

    private function generateParameters($route): array
    {
        $parameters = [];

        // Path parameters
        preg_match_all('/\{([^}]+)\}/', $route->uri(), $matches);
        foreach ($matches[1] as $param) {
            $parameters[] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string'],
            ];
        }

        // Query parameters from form request if available
        if ($formRequest = $this->getFormRequest($route)) {
            $rules = $formRequest->rules();
            foreach ($rules as $field => $rule) {
                if (!Str::contains($route->uri(), '{' . $field . '}')) {
                    $parameters[] = [
                        'name' => $field,
                        'in' => 'query',
                        'required' => is_array($rule) ? in_array('required', $rule) : Str::contains($rule, 'required'),
                        'schema' => $this->generateParameterSchema($rule),
                    ];
                }
            }
        }

        return $parameters;
    }

    private function generateResponses($route): array
    {
        return [
            '200' => [
                'description' => 'Successful operation',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'data' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'Unauthorized',
            ],
            '403' => [
                'description' => 'Forbidden',
            ],
            '404' => [
                'description' => 'Not Found',
            ],
            '422' => [
                'description' => 'Validation Error',
            ],
        ];
    }

    private function generateRequestBody($route): array
    {
        if ($formRequest = $this->getFormRequest($route)) {
            $rules = $formRequest->rules();
            $properties = [];

            foreach ($rules as $field => $rule) {
                $properties[$field] = $this->generateParameterSchema($rule);
            }

            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $properties,
                        ],
                    ],
                ],
            ];
        }

        return [];
    }

    private function getFormRequest($route)
    {
        if (!$route->controller) {
            return null;
        }

        $method = new ReflectionMethod(
            $route->controller,
            $route->getActionMethod()
        );

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && is_subclass_of($type->getName(), 'Illuminate\Foundation\Http\FormRequest')) {
                return new ($type->getName());
            }
        }

        return null;
    }

    private function generateParameterSchema($rule): array
    {
        $rule = is_array($rule) ? implode('|', $rule) : $rule;

        if (Str::contains($rule, 'numeric')) {
            return ['type' => 'number'];
        }

        if (Str::contains($rule, 'integer')) {
            return ['type' => 'integer'];
        }

        if (Str::contains($rule, 'boolean')) {
            return ['type' => 'boolean'];
        }

        if (Str::contains($rule, 'array')) {
            return ['type' => 'array', 'items' => ['type' => 'string']];
        }

        return ['type' => 'string'];
    }

    private function extractSchemas($controller): array
    {
        $schemas = [];
        $reflection = new ReflectionClass($controller);
        
        foreach ($reflection->getMethods() as $method) {
            $docComment = $method->getDocComment();
            if ($docComment) {
                // Extract @response and @request annotations
                preg_match_all('/@(response|request)\s+(\w+)\s*{([^}]+)}/s', $docComment, $matches);
                
                foreach ($matches[2] as $index => $schemaName) {
                    $schemas[$schemaName] = [
                        'type' => 'object',
                        'properties' => $this->parseSchemaProperties($matches[3][$index]),
                    ];
                }
            }
        }

        return $schemas;
    }

    private function parseSchemaProperties(string $definition): array
    {
        $properties = [];
        $lines = explode("\n", trim($definition));

        foreach ($lines as $line) {
            if (preg_match('/(\w+):\s*(\w+)/', trim($line), $matches)) {
                $properties[$matches[1]] = ['type' => strtolower($matches[2])];
            }
        }

        return $properties;
    }
} 