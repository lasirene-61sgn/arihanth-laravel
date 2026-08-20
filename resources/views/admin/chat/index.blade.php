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
                       data-title="{{ $otherUser->name ?? ('Conversation #' . $convo->id) }}"
                       onclick="window.loadConversation({{ (int) $convo->id }}, this.getAttribute('data-title'))">
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
                <form id="chat-form" class="d-flex gap-2" onsubmit="window.handleSendMessage(event)">
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

<!-- Modal: Start New Chat for Admin -->
<div class="modal fade" id="newAdminChatModal" tabindex="-1" aria-labelledby="newAdminChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newAdminChatModalLabel">Start Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                    @if(isset($suggestedContacts) && count($suggestedContacts) > 0)
                        @foreach($suggestedContacts as $contact)
                            @php $contactObj = is_array($contact) ? (object)$contact : $contact; @endphp
                            <a href="{{ route('admin.chat.start', ['receiverId' => $contactObj->id ?? $contactObj->user_id]) }}" 
                               class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $contactObj->name ?? 'Contact' }}</h6>
                                    <small class="text-muted">{{ $contactObj->email ?? '' }}</small>
                                </div>
                                <span class="btn btn-sm btn-primary">Start Chat</span>
                            </a>
                        @endforeach
                    @else
                        <div class="p-4 text-center text-muted">No contacts available to message.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .convo-item.active { background-color: #e9ecef !important; border-left: 4px solid #0d6efd; }
    .msg-bubble { max-width: 70%; padding: 10px 14px; border-radius: 12px; margin-bottom: 12px; word-wrap: break-word; }
    .msg-outgoing { background-color: #0d6efd; color: #fff; margin-left: auto; border-bottom-right-radius: 2px; }
    .msg-incoming { background-color: #e4e6eb; color: #1c1e21; margin-right: auto; border-bottom-left-radius: 2px; }
    .msg-time { font-size: 0.72rem; opacity: 0.8; margin-top: 3px; text-align: right; }
</style>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
    const authUserId = {{ (int) $user->id }};
    const authUserType = "{{ addslashes(get_class($user)) }}";
    const sendRouteUrl = "{{ route('admin.chat.send') }}";
    const showRouteBaseUrl = "{{ url('admin/chat') }}";

    let activeConversationId = null;

    // Initialize Echo
    window.Pusher = Pusher;
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost: '{{ config("broadcasting.connections.reverb.options.host", "localhost") }}',
            wsPort: {{ (int) config("broadcasting.connections.reverb.options.port", 8080) }},
            wssPort: {{ (int) config("broadcasting.connections.reverb.options.port", 8080) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });
    } catch (e) {
        console.error("Echo init error:", e);
    }

    // Global Load Conversation
    window.loadConversation = function(conversationId, titleName) {
        if (window.Echo && activeConversationId) {
            window.Echo.leave(`conversation.${activeConversationId}`);
        }

        activeConversationId = conversationId;
        
        const inputId = document.getElementById('active_conversation_id');
        const msgInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const chatTitle = document.getElementById('chat-title');
        const chatSubtitle = document.getElementById('chat-subtitle');

        if (inputId) inputId.value = conversationId;
        if (msgInput) msgInput.disabled = false;
        if (sendBtn) sendBtn.disabled = false;
        if (chatTitle) chatTitle.innerText = titleName;
        if (chatSubtitle) chatSubtitle.innerText = 'Connected in real-time';

        document.querySelectorAll('.convo-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.querySelector(`.convo-item[data-id="${conversationId}"]`);
        if (activeItem) activeItem.classList.add('active');

        const chatBox = document.getElementById('chat-messages');
        if (chatBox) {
            chatBox.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted">Loading messages...</div>';
        }

        fetch(`${showRouteBaseUrl}/${conversationId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!chatBox) return;
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
            if (chatBox) chatBox.innerHTML = '<div class="text-danger text-center pt-5">Error loading messages.</div>';
        });

        // Listen on private channel
        if (window.Echo) {
            window.Echo.private(`conversation.${conversationId}`)
                .listen('.message.sent', (e) => {
                    appendMessage(e);
                    scrollToBottom();
                });
        }
    };

    // Global Send Message
    window.handleSendMessage = function(e) {
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
                'X-Socket-ID': (window.Echo && window.Echo.socketId) ? window.Echo.socketId() : ''
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
    };

    function appendMessage(msg) {
        const chatBox = document.getElementById('chat-messages');
        if (!chatBox) return;

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
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endsection