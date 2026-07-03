<?php

declare(strict_types=1);

namespace App\Platform\DAM\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Platform\DAM\Application\Actions\UploadAssetAction;
use App\Platform\DAM\Application\Actions\AttachAssetAction;
use App\Platform\DAM\Application\Actions\CreateFolderAction;
use App\Platform\DAM\Application\Queries\ListAssetsQuery;
use App\Platform\DAM\Application\Queries\GetAssetDetailsQuery;
use App\Platform\DAM\Application\Services\DAMService;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Enums\AttachmentRole;
use App\Platform\DAM\Presentation\Requests\UploadAssetRequest;
use App\Platform\DAM\Presentation\Resources\AssetResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    protected DAMService $damService;

    public function __construct(DAMService $damService)
    {
        $this->damService = $damService;
    }

    /**
     * List and search assets.
     */
    public function index(Request $request, ListAssetsQuery $query): JsonResponse
    {
        Gate::authorize('viewAny', Asset::class);

        $assets = $query->execute($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Assets retrieved successfully.',
            'data' => AssetResource::collection($assets->items()),
            'errors' => [],
            'meta' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'per_page' => $assets->perPage(),
                'total' => $assets->total(),
            ]
        ]);
    }

    /**
     * Upload a new asset.
     */
    public function store(UploadAssetRequest $request): JsonResponse
    {
        Gate::authorize('create', Asset::class);

        $file = $request->file('file');
        $title = $request->input('title');
        $description = $request->input('description');
        $folderId = $request->input('folder_id');

        $asset = $this->damService->upload($file, $title, $description, $folderId);

        // Load relations for response
        $asset->load(['currentVersion.file']);

        return response()->json([
            'success' => true,
            'message' => 'Asset uploaded and queued for processing successfully.',
            'data' => new AssetResource($asset),
            'errors' => [],
            'meta' => [],
        ], 201);
    }

    /**
     * Retrieve asset details.
     */
    public function show(string $id, GetAssetDetailsQuery $query): JsonResponse
    {
        $asset = $query->execute($id);

        Gate::authorize('view', $asset);

        // Increment view count
        $asset->increment('view_count');

        return response()->json([
            'success' => true,
            'message' => 'Asset details retrieved successfully.',
            'data' => new AssetResource($asset),
            'errors' => [],
            'meta' => [],
        ]);
    }

    /**
     * Attach asset polymorphically to a business model.
     */
    public function attach(Request $request, string $id, AttachAssetAction $action): JsonResponse
    {
        $asset = Asset::findOrFail($id);
        Gate::authorize('update', $asset);

        $request->validate([
            'attachable_type' => ['required', 'string'],
            'attachable_id' => ['required', 'string'],
            'attachment_role' => ['required', 'string'],
        ]);

        $type = $request->input('attachable_type');
        $targetId = $request->input('attachable_id');
        $roleStr = $request->input('attachment_role');

        $role = AttachmentRole::from($roleStr);

        if (!class_exists($type)) {
            return response()->json([
                'success' => false,
                'message' => "Target entity class [{$type}] does not exist.",
                'errors' => ['attachable_type' => ['Invalid target entity class']],
                'meta' => [],
            ], 422);
        }

        $entity = $type::findOrFail($targetId);

        $attachment = $action->execute($asset->id, $entity, $role);

        return response()->json([
            'success' => true,
            'message' => 'Asset attached successfully.',
            'data' => [
                'attachment_id' => $attachment->id,
                'asset_id' => $attachment->asset_id,
                'attachable_type' => $attachment->attachable_type,
                'attachable_id' => $attachment->attachable_id,
                'attachment_role' => $attachment->attachment_role->value,
            ],
            'errors' => [],
            'meta' => [],
        ]);
    }

    /**
     * Create a directory folder.
     */
    public function createFolder(Request $request, CreateFolderAction $action): JsonResponse
    {
        Gate::authorize('create', Asset::class);

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'string', 'uuid', 'exists:dam_folders,id'],
        ]);

        $folder = $action->execute(
            $request->input('name'),
            $request->input('parent_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully.',
            'data' => [
                'id' => $folder->id,
                'name' => $folder->name,
                'parent_id' => $folder->parent_id,
            ],
            'errors' => [],
            'meta' => [],
        ], 201);
    }

    /**
     * Generate signed temporary download URL.
     */
    public function generateSignedUrl(Request $request, string $id): JsonResponse
    {
        $asset = Asset::findOrFail($id);
        Gate::authorize('view', $asset);

        $file = $asset->currentVersion?->file;
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Asset does not have an active physical file.',
                'errors' => ['file' => ['Active physical file missing']],
                'meta' => [],
            ], 404);
        }

        $expires = $request->input('expires_minutes', 15);
        $url = $this->damService->generateTemporaryUrl($file->path, (int) $expires);

        // Increment download counter
        $asset->increment('download_count');

        return response()->json([
            'success' => true,
            'message' => 'Signed URL generated successfully.',
            'data' => [
                'url' => $url,
                'expires_at' => now()->addMinutes((int) $expires)->toIso8601String()
            ],
            'errors' => [],
            'meta' => [],
        ]);
    }
}
