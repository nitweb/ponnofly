<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        return $this->render($request, null);
    }

    public function show(Request $request, ContactMessage $message): View
    {
        if ($message->status === 'unread') {
            $message->update(['status' => 'read']);
        }

        return $this->render($request, $message);
    }

    public function reply(Request $request, ContactMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $message->update([
            'reply' => $data['reply'],
            'status' => 'replied',
            'replied_at' => now(),
        ]);

        try {
            Mail::raw($data['reply'], fn ($mail) => $mail
                ->to($message->email, $message->name)
                ->subject('Re: '.$message->topic));
        } catch (\Throwable $exception) {
            report($exception);

            return to_route('admin.messages.show', $message)
                ->with('error', 'Reply saved, but the email could not be sent.');
        }

        return to_route('admin.messages.show', $message)->with('success', 'Reply sent.');
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

    private function render(Request $request, ?ContactMessage $selected): View
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
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'selected' => $selected,
            'filters' => $filters,
            'unreadCount' => ContactMessage::unread()->count(),
        ]);
    }
}
