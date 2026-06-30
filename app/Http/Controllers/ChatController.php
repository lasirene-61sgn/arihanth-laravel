<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\ProcessOwner;
use App\Services\ChatService;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Determine the current authenticated user and their guard.
     */
    private function getAuthUser()
    {
        $segment = request()->segment(1);
        
        // Map URL segment to guard
        $guardMap = [
            'super-admin' => 'super_admin',
            'admin' => 'admin',
            'buyer' => 'buyer',
            'craftsman' => 'craftsman',
            'key-user' => 'key_user',
        ];

        // Check the guard matching the current URL segment first
        if (isset($guardMap[$segment]) && Auth::guard($guardMap[$segment])->check()) {
            return Auth::guard($guardMap[$segment])->user();
        }

        // Fallback to checking all guards in order
        $guards = ['super_admin', 'admin', 'buyer', 'craftsman', 'key_user', 'web'];
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        abort(401, 'Unauthorized');
    }

    /**
     * Display the chat interface.
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $isSuperAdmin = $this->chatService->isSuperAdmin($user);
        $isAdmin = ($user instanceof ProcessOwner && $user->role === 'admin');

        $conversations = $this->chatService->getConversations($user);
        $all_conversations = $isSuperAdmin ? Conversation::with(['sender', 'receiver', 'messages' => fn($q) => $q->latest()->limit(1)])->orderBy('updated_at', 'desc')->get() : $conversations;
        
        $admins = $this->chatService->getMonitoringData($user);

        $buyerConversations = collect();
        $craftsmanConversations = collect();

        foreach ($conversations as $convo) {
            $otherType = ($convo->sender_id == $user->id && $convo->sender_type == get_class($user)) 
                ? $convo->receiver_type 
                : $convo->sender_type;
            
            if ($otherType === \App\Models\Buyer::class) {
                $buyerConversations->push($convo);
            } elseif ($otherType === \App\Models\Craftman::class) {
                $craftsmanConversations->push($convo);
            }
        }

        // Suggested contacts logic (keeping it simple for web)
        $suggestedContacts = $this->chatService->searchUsers('', $user);

        return view('chat.index', compact(
            'conversations', 
            'all_conversations', 
            'suggestedContacts', 
            'admins', 
            'isSuperAdmin', 
            'isAdmin',
            'buyerConversations',
            'craftsmanConversations'
        ));
    }

    /**
     * Search users via AJAX.
     */
    public function searchUsers(Request $request)
    {
        $user = $this->getAuthUser();
        return response()->json($this->chatService->searchUsers($request->get('q'), $user));
    }

    /**
     * Show messages for a specific conversation.
     */
    public function show(Conversation $conversation)
    {
        $user = $this->getAuthUser();
        try {
            $messages = $this->chatService->getMessages($conversation, $user, true); // true for base64
            
            // Map messages for the web view
            $messages = $messages->map(function ($msg) {
                $msg->attachments->map(function ($at) {
                    $at->url = asset('storage/' . $at->file_path);
                    return $at;
                });
                return $msg;
            });

            return response()->json(['conversation' => $conversation, 'messages' => $messages]);
        } catch (\Exception $e) {
            abort($e->getCode() ?: 500, $e->getMessage());
        }
    }

    /**
     * Store a new message.
     */
    public function store(Request $request)
    {
        $user = $this->getAuthUser();
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        try {
            $message = $this->chatService->storeMessage($request->all(), $user);
            broadcast(new MessageSent($message))->toOthers();
            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Start a chat.
     */
    public function startChat($receiverId, $type = null)
    {
        $user = $this->getAuthUser();
        $segment = request()->segment(1);
        
        $this->chatService->startConversation($receiverId, $type, $user);

        return redirect()->route($segment . '.chat.index');
    }

    /**
     * Delete a message.
     */
    public function destroy($id)
    {
        $user = $this->getAuthUser();
        try {
            $this->chatService->deleteMessage($id, $user);
            return response()->json(['message' => 'Message deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Delete an entire conversation.
     */
    public function destroyConversation($id)
    {
        $user = $this->getAuthUser();
        try {
            $this->chatService->deleteConversation($id, $user);
            return response()->json(['message' => 'Conversation deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
