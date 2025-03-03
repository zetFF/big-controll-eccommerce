<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiDoc;
use App\Services\ApiDocumentationService;
use Illuminate\Http\Request;

class ApiDocController extends BaseController
{
    public function __construct(
        private ApiDocumentationService $docService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin'])->except(['show', 'latest']);
    }

    public function index(Request $request)
    {
        $docs = ApiDoc::with('creator')
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($docs);
    }

    public function generate()
    {
        $spec = $this->docService->generateDocumentation();
        $doc = $this->docService->publishDocumentation($spec);

        return $this->successResponse($doc, 201);
    }

    public function show(ApiDoc $apiDoc)
    {
        return $this->successResponse($apiDoc);
    }

    public function latest()
    {
        $doc = ApiDoc::where('is_published', true)
            ->latest('published_at')
            ->firstOrFail();

        return $this->successResponse($doc);
    }

    public function destroy(ApiDoc $apiDoc)
    {
        $apiDoc->delete();
        return $this->successResponse(['message' => 'API documentation deleted successfully']);
    }
} 