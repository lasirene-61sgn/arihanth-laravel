<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Chat\BaseWebChatController;
use Illuminate\Http\Request;

class ChatController extends BaseWebChatController
{
    protected string $panel = 'buyer';
    protected string $guard = 'buyer';

    public function index(Request $request)
    {
        $user = $this->getAuthUser();
        $pusherKey = config('broadcasting.connections.pusher.key') ?? env('PUSHER_APP_KEY');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster') ?? env('PUSHER_APP_CLUSTER', 'ap2');

        return view('buyer.chat.index', [
            'authUser'      => $this->formatUserSummary($user),
            'isSuperAdmin'  => false,
            'panel'         => $this->panel,
            'pusherKey'     => $pusherKey,
            'pusherCluster' => $pusherCluster,
        ]);
    }
}

