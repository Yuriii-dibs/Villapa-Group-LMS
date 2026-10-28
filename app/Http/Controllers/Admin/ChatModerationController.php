<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatModerationController extends Controller
{
    /**
     * Display a listing of recent chat messages across course rooms and direct messages for moderation.
     */
    public function index(Request $request): View
    {
        $messages = Message::with(['sender', 'recipient', 'course'])
            ->latest()
            ->paginate(30);

        return view('admin.chat-moderation.index', compact('messages'));
    }

    /**
     * Delete a flagged or inappropriate message.
     */
    public function destroy(Message $message): RedirectResponse
    {
        $message->delete();

        return back()->with('status', 'Message deleted by administrator.');
    }
}
