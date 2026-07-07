<?php

declare(strict_types=1);

namespace App\Modules\IoT\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Modules\IoT\Domain\Services\HmacAuthenticator;
use App\Modules\IoT\Domain\Services\TelemetryProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetryController extends BaseApiController
{
    public function __construct(
        protected HmacAuthenticator $authenticator,
        protected TelemetryProcessor $processor
    ) {}

    public function heartbeat(Request $request): JsonResponse
    {
        $device = $this->authenticateRequest($request);

        $heartbeat = $this->processor->processHeartbeat($device, $request->all());

        return response()->json([
            'message' => 'Heartbeat received.',
            'heartbeat' => $heartbeat,
        ]);
    }

    public function telemetry(Request $request): JsonResponse
    {
        $device = $this->authenticateRequest($request);

        $log = $this->processor->process($device, $request->all());

        return response()->json([
            'message' => 'Telemetry log processed.',
            'log' => $log,
        ]);
    }

    protected function authenticateRequest(Request $request): \App\Modules\IoT\Domain\Entities\Device
    {
        $serial = $request->header('X-Device-Key');
        $timestamp = $request->header('X-Device-Timestamp');
        $nonce = $request->header('X-Device-Nonce');
        $signature = $request->header('X-Device-Signature');
        $rawContent = $request->getContent();

        if (!$serial || !$timestamp || !$nonce || !$signature) {
            abort(401, 'Missing HMAC authentication headers.');
        }

        return $this->authenticator->authenticate($serial, $timestamp, $nonce, $signature, $rawContent);
    }
}
