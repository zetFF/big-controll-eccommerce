<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Health\Health;
use Spatie\Health\ResultStores\ResultStore;

class HealthController extends Controller
{
    public function __construct(
        private Health $health,
        private ResultStore $resultStore
    ) {}

    public function index()
    {
        $checkResults = $this->health->checkAll();
        $this->resultStore->save($checkResults);

        return view('admin.health.index', [
            'checkResults' => $checkResults,
            'lastRanAt' => $this->resultStore->latestResults()?->first()?->created_at,
            'historicalResults' => $this->resultStore->latestResults()
        ]);
    }
} 