@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row" style="height: calc(100vh - 140px);">

        <!-- Conversations Sidebar -->
        <div class="col-md-4 col-lg-3 p-0 bg-white border-end d-flex flex-column h-100 shadow-sm">
            <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-dark fw-bold">Admin Chat</h5>
                    <small class="text-muted">Logged in as: <strong>{{ $user->name ?? 'Admin' }}</strong></small>
                </div>
                <button type="button" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#newAdminChatModal" title="New Message">
                    +
                </button>
            </div>

            <div class="list-group list-group-flush overflow-auto flex-grow-1" id="admin-convo-list">
                @forelse($conversations as $convo)
                @php
                $otherUser = ($convo->sender_id == $user->id && $convo->sender_type == get_class($user))
                ? $convo->receiver
                : $convo->sender;
                @endphp
                <a href="javascript:void(0)"
                    class="list-group-item list-group-item-action convo-item p-3 border-bottom"
                    data-id="{{ $convo->id }}"
                    onclick="loadConversation({{ $convo->id }}, '{{ addslashes($otherUser->name ?? ('Conversation #' . $convo->id)) }}')">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-truncate">{{ $otherUser->name ?? ('Conversation #' . $convo->id) }}</strong>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            {{ $convo->updated_at ? $convo->updated_at->diffForHumans(null, true) : '' }}
                        </small>
                    </div>
                    <small class="text-muted text-truncate d-block">
                        {{ $convo->messages->first()->body ?? 'Click to open conversation' }}
                    </small>
                </a>
                @empty
                <div class="p-4 text-center text-muted">
                    <p class="mb-2">No conversations found.</p>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newAdminChatModal">Start New Chat</button>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Chat Conversation Area -->
        <div class="col-md-8 col-lg-9 p-0 bg-white d-flex flex-column h-100 shadow-sm">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold" id="chat-title">Select a conversation</h6>
                    <small class="text-muted" id="chat-subtitle">No active chat</small>
                </div>
            </div>

            <!-- Messages Stream Area -->
            <div class="flex-grow-1 overflow-auto p-4" id="chat-messages" style="background-color: #f8f9fa; position: relative;">
                <div class="d-flex h-100 align-items-center justify-content-center text-muted flex-column">
                    <p class="mb-0">Select a conversation from the sidebar to start messaging.</p>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="p-3 border-top bg-white">
                <form id="chat-form" class="d-flex gap-2" onsubmit="handleSendMessage(event)">
                    <input type="hidden" id="active_conversation_id" value="">

                    <input type="text"
                        id="message-input"
                        class="form-control"
                        placeholder="Type a message..."
                        autocomplete="off"
                        disabled>

                    <button type="submit" class="btn btn-primary px-4" id="send-btn" disabled>
                        Send
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
    .convo-item.active {
        background-color: #e9ecef !important;
        border-left: 4px solid #0d6efd;
    }

    .msg-bubble {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 12px;
        margin-bottom: 12px;
        word-wrap: break-word;
    }

    .msg-outgoing {
        background-color: #0d6efd;
        color: #fff;
        margin-left: auto;
        border-bottom-right-radius: 2px;
    }

    .msg-incoming {
        background-color: #e4e6eb;
        color: #1c1e21;
        margin-right: auto;
        border-bottom-left-radius: 2px;
    }

    .msg-time {
        font-size: 0.72rem;
        opacity: 0.8;
        margin-top: 3px;
        text-align: right;
    }
</style>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
    const authUserId = {
        {
            (int) $user - > id
        }
    };
    const authUserType = "{{ addslashes(get_class($user)) }}";
    const sendRouteUrl = "{{ route('admin.chat.send') }}";
    const showRouteBaseUrl = "{{ url('admin/chat') }}";

    let activeConversationId = null;

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: '{{ config("broadcasting.connections.reverb.key") }}',
        wsHost: '{{ config("broadcasting.connections.reverb.options.host", "localhost") }}',
        wsPort: {
            {
                (int) config("broadcasting.connections.reverb.options.port", 8080)
            }
        },
        wssPort: {
            {
                (int) config("broadcasting.connections.reverb.options.port", 8080)
            }
        },
        forceTLS: {
            {
                config("broadcasting.connections.reverb.options.useTLS") ? 'true' : 'false'
            }
        },
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }
    });

    function loadConversation(conversationId, titleName) {
        if (activeConversationId) {
            window.Echo.leave(`conversation.${activeConversationId}`);
        }

        activeConversationId = conversationId;
        document.getElementById('active_conversation_id').value = conversationId;
        document.getElementById('message-input').disabled = false;
        document.getElementById('send-btn').disabled = false;
        document.getElementById('chat-title').innerText = titleName;
        document.getElementById('chat-subtitle').innerText = 'Connected in real-time';

        document.querySelectorAll('.convo-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.querySelector(`.convo-item[data-id="${conversationId}"]`);
        if (activeItem) activeItem.classList.add('active');

        const chatBox = document.getElementById('chat-messages');
        chatBox.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted">Loading messages...</div>';

        fetch(`${showRouteBaseUrl}/${conversationId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                chatBox.innerHTML = '';
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => appendMessage(msg));
                } else {
                    chatBox.innerHTML = '<div class="text-center text-muted pt-5">No messages yet. Send a message to start!</div>';
                }
                scrollToBottom();
            })
            .catch(err => {
                console.error('Fetch error:', err);
                chatBox.innerHTML = '<div class="text-danger text-center pt-5">Error loading messages.</div>';
            });

        // Listen on private channel
        window.Echo.private(`conversation.${conversationId}`)
            .listen('.message.sent', (e) => {
                appendMessage(e);
                scrollToBottom();
            });
    }

    function handleSendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const text = input.value.trim();
        const convoId = document.getElementById('active_conversation_id').value;

        if (!text || !convoId) return;

        input.value = '';

        fetch(sendRouteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Socket-ID': window.Echo ? window.Echo.socketId() : ''
                },
                body: JSON.stringify({
                    conversation_id: convoId,
                    body: text
                })
            })
            .then(res => res.json())
            .then(msg => {
                appendMessage({
                    id: msg.id,
                    sender_id: authUserId,
                    sender_type: authUserType,
                    body: msg.body,
                    sender_name: 'You',
                    created_at: 'Just now'
                });
                scrollToBottom();
            })
            .catch(err => console.error('Send Error:', err));
    }

    function appendMessage(msg) {
        const chatBox = document.getElementById('chat-messages');
        const placeholder = chatBox.querySelector('.text-muted, .text-center');
        if (placeholder) placeholder.remove();

        if (msg.id && document.getElementById(`msg-${msg.id}`)) {
            return;
        }

        const isMe = (parseInt(msg.sender_id) === parseInt(authUserId)) &&
            (!msg.sender_type || msg.sender_type === authUserType);

        const bubble = document.createElement('div');
        if (msg.id) bubble.id = `msg-${msg.id}`;

        bubble.className = `msg-bubble ${isMe ? 'msg-outgoing' : 'msg-incoming'}`;
        bubble.innerHTML = `
            <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 2px;">
                ${isMe ? 'You' : (msg.sender_name || 'SuperAdmin')}
            </div>
            <div>${msg.body ?? ''}</div>
            <div class="msg-time">${msg.created_at || ''}</div>
        `;
        chatBox.appendChild(bubble);
    }

    function scrollToBottom() {
        const chatBox = document.getElementById('chat-messages');
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endsection