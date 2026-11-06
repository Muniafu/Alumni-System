<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    /**
     * Send a message in a conversation (participants only — policy enforced).
     *
     * authorize('view', ...) is the participants-only check: the
     * ConversationPolicy only allows actual participants, so no
     * non-participant can ever post or see a conversation.
     */
    public function store(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $request->validate([
            'body' => 'required|string|min:1',
        ]);

        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        // Update conversation updated_at to bump it in the list
        $conversation->touch();

        // Notify other participants
        $recipients = $conversation->users()->where('users.id', '!=', auth()->id())->get();
        Notification::send($recipients, new NewMessageNotification($message));

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Message sent successfully');
    }

    /**
     * Delete a message (author only — policy enforced).
     */
    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);

        $conversation = $message->conversation;
        $message->delete();

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Message deleted successfully');
    }
}
