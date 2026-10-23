<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user('admin');

        return view('admin.notifications.index', [
            'notifications' => $admin->notifications()->paginate(20),
            'unreadCount' => $admin->unreadNotifications()->count(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $admin = $request->user('admin');

        return response()->json([
            'count' => $admin->unreadNotifications()->count(),
            'html' => view('admin.notifications._list', ['items' => $admin->notifications()->limit(8)->get()])->render(),
        ]);
    }

    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user('admin')->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('admin.dashboard', absolute: false));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user('admin')->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
