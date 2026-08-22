<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\MessageStatus;
use App\Events\MessageDelivered;
use App\Events\MessageRead;

class MessageStatusController extends Controller
{
    public function markAsDelivered(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id'
        ]);

        $user = auth()->user();
        $now = now();

        foreach ($request->message_ids as $messageId) {
            $message = Message::find($messageId);

            // Don't mark own messages
            if ($message->sender_id == $user->id && $message->sender_type == get_class($user)) {
                continue;
            }

            $status = MessageStatus::firstOrCreate(
                ['message_id' => $messageId, 'user_id' => $user->id, 'user_type' => get_class($user)]
            );

            if (!$status->delivered_at) {
                $status->update(['delivered_at' => $now]);
                broadcast(new MessageDelivered($message, $user->id, get_class($user), $now))->toOthers();
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id'
        ]);

        $user = auth()->user();
        $now = now();

        foreach ($request->message_ids as $messageId) {
            $message = Message::find($messageId);

            // Don't mark own messages
            if ($message->sender_id == $user->id && $message->sender_type == get_class($user)) {
                continue;
            }

            $status = MessageStatus::firstOrCreate(
                ['message_id' => $messageId, 'user_id' => $user->id, 'user_type' => get_class($user)]
            );

            if (!$status->delivered_at) {
                $status->delivered_at = $now;
            }
            
            if (!$status->read_at) {
                $status->update(['read_at' => $now]);
                broadcast(new MessageRead($message, $user->id, get_class($user), $now))->toOthers();
            }
        }

        return response()->json(['status' => 'success']);
    }
}
