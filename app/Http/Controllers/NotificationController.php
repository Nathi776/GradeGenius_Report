<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(15);
        $unreadCount = $user->unreadNotifications()->count();

        $view = match ($user->role) {
            'parent' => 'notifications.parent-index',
            'teacher' => 'notifications.teacher-index',
            default => 'notifications.index',
        };

        return view($view, [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'role' => $user->role,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data;
        if (isset($data['action_url']) && is_string($data['action_url'])) {
            return redirect($data['action_url'])->with('status', 'Notification marked as read.');
        }

        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }
}
