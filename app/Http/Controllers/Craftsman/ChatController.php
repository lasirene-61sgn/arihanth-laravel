<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Chat\BaseWebChatController;
use Illuminate\Http\Request;

class ChatController extends BaseWebChatController
{
    protected string $panel = 'craftsman';
    protected string $guard = 'craftsman';

    public function index(Request $request)
    {
        $user = $this->getAuthUser();
        $pusherKey = config('broadcasting.connections.pusher.key') ?? env('PUSHER_APP_KEY');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster') ?? env('PUSHER_APP_CLUSTER', 'ap2');

        return view('craftsman.chat.index', [
            'authUser'      => $this->formatUserSummary($user),
            'isSuperAdmin'  => false,
            'panel'         => $this->panel,
            'pusherKey'     => $pusherKey,
            'pusherCluster' => $pusherCluster,
        ]);
    }
}

