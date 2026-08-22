<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get authenticated SuperAdmin instance.
     */
    private function getAuthUser()
    {
        return Auth::guard('super_admin')->user() ?? Auth::user();
    }

    /**
     * Display chat dashboard for SuperAdmin.
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $conversations = $this->chatService->getConversations($user);
        
        $all_conversations = Conversation::with(['sender', 'receiver', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('updated_at', 'desc')
            ->get();
            
        $admins = $this->chatService->getMonitoringData($user);
        $suggestedContacts = $this->chatService->searchUsers('', $user);

        return view('super-admin.chat.index', compact(
            'conversations', 
            'all_conversations', 
            'suggestedContacts', 
            'admins', 
            'user'
        ));
    }

    /**
     * Fetch conversation messages via AJAX.
     */
    public function show(Conversation $conversation)
    {
        $user = $this->getAuthUser();
        $messages = $this->chatService->getMessages($conversation, $user, true);

        $messages = $messages->map(function ($msg) {
            $msg->attachments->map(function ($at) {
                $at->url = asset('storage/' . $at->file_path);
                return $at;
            });
            return $msg;
        });

        return response()->json([
            'conversation' => $conversation,
            'messages'     => $messages
        ]);
    }

    /**
     * Store and broadcast message to others in the conversation.
     */
    public function store(Request $request)
    {
        $user = $this->getAuthUser();
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body'            => 'nullable|string',
            'attachments.*'   => 'nullable|file|max:10240',
        ]);

        $message = $this->chatService->storeMessage($request->all(), $user);
        
        // broadcast to everyone on this channel EXCEPT the sender
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    /**
     * Start conversation with a selected admin or contact.
     */
    public function startChat($receiverId, $type = null)
    {
        $user = $this->getAuthUser();
        $this->chatService->startConversation($receiverId, $type, $user);

        return redirect()->route('super-admin.chat.index');
    }

    public function searchUsers(Request $request)
    {
        $user = $this->getAuthUser();
        $query = $request->input('q', '');
        
        $results = $this->chatService->searchUsers($query, $user);
        return response()->json($results);
    }
}