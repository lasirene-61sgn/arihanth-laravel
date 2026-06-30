<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Http\Resources\Chat\ConversationResource;
use App\Http\Resources\Chat\MessageResource;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Display a listing of conversations.
     */
    public function index(Request $request)
    {
        $conversations = $this->chatService->getConversations($request->user());
        return ConversationResource::collection($conversations);
    }

    /**
     * Display messages for a specific conversation.
     */
    public function show(Request $request, Conversation $conversation)
    {
        try {
            $messages = $this->chatService->getMessages($conversation, $request->user());
            return MessageResource::collection($messages);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a new message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        try {
            $message = $this->chatService->storeMessage($request->all(), $request->user());
            
            // Broadcast the message
            broadcast(new MessageSent($message))->toOthers();

            return new MessageResource($message);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Search for users to chat with.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $results = $this->chatService->searchUsers($query, $request->user());
        return response()->json($results);
    }

    /**
     * Start a new chat.
     */
    public function start(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'receiver_type' => 'required|in:buyer,craftsman,admin,super-admin',
        ]);

        $conversation = $this->chatService->startConversation(
            $request->receiver_id,
            $request->receiver_type,
            $request->user()
        );

        return new ConversationResource($conversation);
    }

    /**
     * Delete a message.
     */
    public function destroy($id, Request $request)
    {
        try {
            $this->chatService->deleteMessage($id, $request->user());
            return response()->json(['message' => 'Message deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Delete an entire conversation.
     */
    public function destroyConversation($id, Request $request)
    {
        try {
            $this->chatService->deleteConversation($id, $request->user());
            return response()->json(['message' => 'Conversation deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
