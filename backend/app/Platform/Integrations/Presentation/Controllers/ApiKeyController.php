<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Integrations\Domain\ApiKeys\ApiKey;
use App\Platform\Integrations\Application\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiKeyController extends BaseApiController
{
    public function __construct(
        protected ApiKeyService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $keys = ApiKey::where('user_id', $userId)->get();

        return $this->successResponse($keys, 'Developer API Keys list.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'scopes' => 'nullable|array',
            'scopes.*' => 'required|string',
            'is_test' => 'nullable|boolean',
        ]);

        $userId = (string) $request->user()->id;
        $scopes = $request->input('scopes', []);

        $result = $this->service->createKey(
            $userId,
            $request->input('name'),
            $scopes,
            (bool) $request->input('is_test', false)
        );

        return $this->successResponse([
            'api_key' => $result['apiKey'],
            'plain_text_key' => $result['plainTextKey'],
        ], 'API Key generated successfully. Save this secret now.', 201);
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $this->service->revokeKey($id, $userId);

        return $this->successResponse(null, 'API Key revoked successfully.');
    }
}
