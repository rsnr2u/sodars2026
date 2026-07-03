<?php

declare(strict_types=1);

namespace App\Platform\Notifications\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Platform\Notifications\Domain\Entities\InAppNotification;
use App\Platform\Notifications\Domain\Entities\NotificationPreference;
use App\Platform\Notifications\Presentation\Resources\InAppNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Retrieve In-App notification feed for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated user.',
                'errors' => ['auth' => ['User session is invalid']],
                'meta' => [],
            ], 401);
        }

        $notifications = InAppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully.',
            'data' => InAppNotificationResource::collection($notifications->items()),
            'errors' => [],
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    /**
     * Mark a specific notification alert as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = InAppNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read successfully.',
            'data' => new InAppNotificationResource($notification),
            'errors' => [],
            'meta' => [],
        ]);
    }

    /**
     * Update notification delivery channel preferences for a user.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'category' => ['required', 'string'],
            'channel' => ['required', 'string'],
            'is_enabled' => ['required', 'boolean'],
        ]);

        $category = $request->input('category');
        $channel = $request->input('channel');
        $isEnabled = $request->input('is_enabled');

        $preference = NotificationPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'category' => $category,
                'channel' => $channel,
            ],
            [
                'id' => (string) Str::uuid(),
                'is_enabled' => $isEnabled,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully.',
            'data' => [
                'id' => $preference->id,
                'user_id' => $preference->user_id,
                'category' => $preference->category,
                'channel' => $preference->channel,
                'is_enabled' => $preference->is_enabled,
            ],
            'errors' => [],
            'meta' => [],
        ]);
    }
}
