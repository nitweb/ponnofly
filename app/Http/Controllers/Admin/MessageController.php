<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(ContactMessage::STATUSES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $messages = ContactMessage::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $like = '%'.addcslashes(trim($term), '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('message', 'like', $like));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'filters' => $filters,
            'unreadCount' => ContactMessage::unread()->count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if ($message->status === 'unread') {
            $message->update(['status' => 'read']);
        }

        return view('admin.messages.show', ['message' => $message]);
    }

    public function readAll(): RedirectResponse
    {
        ContactMessage::unread()->update(['status' => 'read']);

        return back()->with('success', 'All messages marked as read.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return to_route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
