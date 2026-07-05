<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonSuccess(['notifications' => NotificationResource::collection($notifications)]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = Notification::where('user_id', Auth::id())->where('is_read', false)->count();

        return $this->jsonSuccess(['count' => $count]);
    }

    public function subscriptions(): JsonResponse
    {
        $ids = Auth::user()->subscriptions()->pluck('neighborhoods.id');

        return $this->jsonSuccess(['subscriptions' => $ids]);
    }

    public function markRead(int $id): JsonResponse
    {
        Notification::where('id', $id)->where('user_id', Auth::id())->update(['is_read' => true]);

        return $this->jsonSuccess();
    }

    public function markAllRead(): JsonResponse
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);

        return $this->jsonSuccess();
    }

    public function saveSubscriptions(Request $request): JsonResponse
    {
        $ids = array_map('intval', $request->input('neighborhoodIds', []));
        Auth::user()->subscriptions()->sync($ids);

        return $this->jsonSuccess(['subscriptions' => $ids]);
    }
}
