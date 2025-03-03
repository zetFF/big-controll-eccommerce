<?php

namespace App\Health\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Facades\Http;

class PaymentGatewayCheck extends Check
{
    protected ?string $name = 'Payment Gateway';

    public function run(): Result
    {
        try {
            $response = Http::timeout(5)
                ->get(config('services.payment.base_url') . '/health');

            if ($response->successful()) {
                return Result::make()
                    ->ok()
                    ->shortSummary('Payment gateway is operational');
            }

            return Result::make()
                ->failed()
                ->shortSummary('Payment gateway returned error status')
                ->meta(['status_code' => $response->status()]);
        } catch (\Exception $e) {
            return Result::make()
                ->failed()
                ->shortSummary('Payment gateway is not responding')
                ->notificationMessage('Payment gateway check failed: ' . $e->getMessage())
                ->meta(['error' => $e->getMessage()]);
        }
    }
} 