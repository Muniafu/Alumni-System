<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    /**
     * List the current user's conversations.
     *
     * NOTE: eager-loads the real `users` relation. The previous code tried
     * to eager-load `otherUsers`, which is an accessor, not a relation —
     * Laravel throws BadMethodCallException and the whole page crashed.
     * The otherUsers accessor reads the loaded `users` collection.
     */
    public function index()
    {
        $conversations = Conversation::with(['latestMessage', 'users'])
            ->forUser(auth()->id())
            ->latest('updated_at')
            ->paginate(10);

        return view('messaging.index', compact('conversations'));
    }

    /**
     * Show the form for starting a new conversation.
     */
    public function create(Request $request)
    {
        $users = User::where('id', '!=', auth()->id())
            ->approved()
            ->get();

        // Directory "Send Message" links arrive as ?recipients[]=ID
        $preselected = $request->query('recipients', []);

        return view('messaging.create', compact('users'));
    }

    /**
     * Start a new conversation with an initial message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            // Server-side guard: only APPROVED, NOT soft-deleted users can be
            // messaged, and never yourself. The create() form already filters
            // approved users, but a crafted request could previously reach
            // any existing user id (Rule::exists queries the table directly,
            // bypassing the model's soft-delete scope — hence whereNull).
            'recipients.*' => [
                Rule::exists('users', 'id')
                    ->where('is_approved', true)
                    ->whereNull('deleted_at'),
                Rule::notIn([auth()->id()]),
            ],
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:1',
        ]);

        [$conversation, $message] = DB::transaction(function () use ($request) {
            $conversation = Conversation::create([
                'subject' => $request->subject,
            ]);

            $participants = array_unique(array_merge($request->recipients, [auth()->id()]));
            $conversation->users()->attach($participants);

            $message = $conversation->messages()->create([
                'user_id' => auth()->id(),
                'body' => $request->message,
            ]);

            return [$conversation, $message];
        });

        // Notify the recipients about the first message — previously a new
        // conversation arrived silently and recipients never learned about it.
        $recipients = $conversation->users()->where('users.id', '!=', auth()->id())->get();
        Notification::send($recipients, new NewMessageNotification($message));

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Conversation started successfully');
    }

    /**
     * Show a conversation (participants only — policy enforced).
     */
    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->markAsReadForUser(auth()->id());

        $messages = $conversation->messages()
            ->with('sender')
            ->latest()
            ->paginate(15);

        return view('messaging.show', compact('conversation', 'messages'));
    }

    /**
     * "Delete" = LEAVE the conversation (participant detaches). The
     * conversation is soft-deleted only when the LAST participant leaves —
     * previously one participant deleting removed it for everyone.
     */
    public function destroy(Conversation $conversation)
    {
        $this->authorize('delete', $conversation);

        DB::transaction(function () use ($conversation) {
            $conversation->users()->detach(auth()->id());

            if ($conversation->users()->doesntExist()) {
                $conversation->delete();
            }
        });

        return redirect()->route('conversations.index')
            ->with('success', 'Conversation left');
    }
}
