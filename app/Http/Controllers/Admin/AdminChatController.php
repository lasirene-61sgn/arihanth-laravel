<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    private function getAuthUser()
    {
        return Auth::guard('admin')->user() ?? Auth::user();
    }

    public function index()
    {
        $user = $this->getAuthUser();
        $conversations = $this->chatService->getConversations($user);
        $suggestedContacts = $this->chatService->searchUsers('', $user);

        return view('admin.chat.index', compact(
            'conversations', 
            'suggestedContacts', 
            'user'
        ));
    }

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

    public function store(Request $request)
    {
        $user = $this->getAuthUser();
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body'            => 'nullable|string',
            'attachments.*'   => 'nullable|file|max:10240',
        ]);

        $message = $this->chatService->storeMessage($request->all(), $user);
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function startChat($receiverId, $type = null)
    {
        $user = $this->getAuthUser();
        $this->chatService->startConversation($receiverId, $type, $user);

        return redirect()->route('admin.chat.index');
    }
}