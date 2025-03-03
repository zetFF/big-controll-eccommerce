<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class BaseController extends Controller
{
    use ApiResponse;

    protected string $version = 'v1';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->header('Accept-Version') && 
                $request->header('Accept-Version') !== $this->version) {
                return $this->errorResponse('API version not supported', 400);
            }
            return $next($request);
        });
    }
} 