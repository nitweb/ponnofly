<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\AdminNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('frontend.contact.index', ['topics' => ContactMessage::TOPICS]);
    }

    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create([
            ...$request->safe()->except('website'),
            'status' => 'unread',
            'ip_address' => $request->ip(),
        ]);

        AdminNotifier::newMessage($message);

        return to_route('contact.show')
            ->with('contact_success', 'Thank you! Your message has been received. We reply within one working day.')
            ->withFragment('contact-form');
    }
}
