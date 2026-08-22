@extends(request()->segment(1) . '.layouts.app')

@section('content')
<style>
    .message-area {
        height: 500px;
        overflow-y: auto;
        padding: 20px;
        background-color: #f8f9fa;
        display: flex;
        flex-direction: column;
    }

    .chat-bubble {
        max-width: 75%;
        padding: 10px 15px;
        border-radius: 20px;
        margin-bottom: 10px;
        font-size: 0.9rem;
        position: relative;
        clear: both;
        word-wrap: break-word;
    }

    .bubble-me {
        background: linear-gradient(135deg, #6d28d9 0%, #4c1d95 100%);
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }

    .bubble-other {
        background: white;
        color: #1e293b;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .recording-overlay {
        display: none;
        align-items: center;
        gap: 15px;
        background: white;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 5;
        padding: 0 20px;
        border-radius: 50px;
    }

    .recording-active .recording-overlay {
        display: flex;
    }

    .dot-pulse {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ef4444;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.9); opacity: 1; }
        70% { transform: scale(1.5); opacity: 0; }
        100% { transform: scale(0.9); opacity: 0; }
    }

    .chat-item {
        cursor: pointer;
        transition: background 0.2s;
        border-left: 4px solid transparent;
    }

    .chat-item.active {
        background-color: #ede9fe;
        border-left-color: #6d28d9;
    }

    .voice-player-container {
        min-width: 220px;
        padding: 5px 0;
    }

    audio {
        height: 35px;
        width: 100%;
        border-radius: 10px;
    }

    .bubble-me audio {
        filter: sepia(20%) saturate(70%) grayscale(1) contrast(99%) invert(12%);
    }

    /* Tree structure for admin monitoring */
    .admin-folder {
        background-color: #f3f0ff;
        color: #6d28d9;
        padding: 6px;
        border-radius: 8px;
        margin-right: 10px;
    }
    .admin-tree-container {
        border-left: 2px solid #e2e8f0;
        margin-left: 20px;
        padding-left: 10px;
    }
    .admin-branch {
        position: relative;
        padding: 8px 0 8px 25px;
        cursor: pointer;
        font-size: 0.85rem;
        color: #475569;
        transition: all 0.2s;
    }
    .admin-branch:hover {
        color: #6d28d9;
        background-color: #f8fafc;
    }
    .admin-branch::before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        width: 15px;
        height: 2px;
        background-color: #e2e8f0;
    }
    .admin-branch i {
        margin-right: 8px;
        color: #94a3b8;
    }
    .admin-sub-convo {
        padding-left: 35px;
        border-left: 2px solid #f1f5f9;
        margin-left: 15px;
    }

    /* Custom Voice Player */
    .custom-voice-player {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        border-radius: 50px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 250px;
        margin-top: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .bubble-other .custom-voice-player {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }
    .play-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #6d28d9;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .play-btn:hover { transform: scale(1.1); }
    .voice-progress {
        flex-grow: 1;
        height: 4px;
        background: rgba(0,0,0,0.1);
        border-radius: 2px;
        position: relative;
    }
    .voice-progress-fill {
        height: 100%;
        background: #6d28d9;
        border-radius: 2px;
        width: 0%;
    }
    .voice-download-link {
        color: inherit;
        opacity: 0.7;
        font-size: 1.1rem;
        transition: opacity 0.2s;
    }
    .voice-download-link:hover { opacity: 1; color: #6d28d9; }
    .chat-item:hover {
        background-color: #f1f5f9;
    }

    .loading-messages {
        opacity: 0.5;
        pointer-events: none;
    }

    .message-spinner {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }

    .loading-messages .message-spinner {
        display: block;
    }
</style>

<div class="container-fluid py-4">
    <div class="row shadow-lg bg-white rounded-4 overflow-hidden mx-0" style="min-height: 600px;">
        <div class="col-md-4 col-lg-3 p-0 border-end">
            <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Messages</h5>
                <button class="btn btn-sm btn-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#newChatModal"><i class="bi bi-plus-lg"></i></button>
            </div>
            @if($isSuperAdmin || $isAdmin)
            <div class="px-3 pt-3">
                <ul class="nav nav-pills nav-fill mb-3 bg-light rounded-pill p-1" id="chatTabs" role="tablist">
                    @if($isAdmin)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill py-1 small fw-bold" id="buyers-tab" data-bs-toggle="pill" data-bs-target="#buyers-pane" type="button" role="tab">Buyers</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill py-1 small fw-bold" id="craftsmen-tab" data-bs-toggle="pill" data-bs-target="#craftsmen-pane" type="button" role="tab">Craftsmen</button>
                    </li>
                    @else
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill py-1 small fw-bold" id="convos-tab" data-bs-toggle="pill" data-bs-target="#convos-pane" type="button" role="tab">Chats</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill py-1 small fw-bold" id="admins-tab" data-bs-toggle="pill" data-bs-target="#admins-pane" type="button" role="tab">Admins</button>
                    </li>
                    @endif
                </ul>
            </div>
            @endif

            <div class="tab-content" id="chat-sidebar-container">
                <div class="tab-pane fade {{ !$isAdmin ? 'show active' : '' }}" id="convos-pane" role="tabpanel">
                    <div class="chat-list" id="chat-sidebar" style="height: 500px; overflow-y: auto;">
                        @forelse($conversations as $chat)
                        @php
                        $me = auth()->user();
                        $isSender = ($chat->sender_id == $me->id && $chat->sender_type == get_class($me));
                        $other = $isSender ? $chat->receiver : $chat->sender;
                        $otherName = $other->name ?? $other->full_name ?? $other->business_name ?? 'User';
                        @endphp
                        <div class="chat-item p-3 border-bottom d-flex align-items-center {{ isset($activeId) && $activeId == $chat->id ? 'active' : '' }}" 
                             id="chat-item-{{ $chat->id }}" 
                             onclick="openChat({{ $chat->id }}, '{{ addslashes($otherName) }}')">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">{{ $otherName }}</h6>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $chat->updated_at->diffForHumans(null, true) }}</small>
                                </div>
                                <small class="text-muted text-truncate d-block" style="max-width: 200px; font-size: 0.75rem;">{{ $chat->messages->last()->body ?? 'Click to chat' }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center p-5 text-muted">No personal chats found.</div>
                        @endforelse
                    </div>
                </div>

                @if($isAdmin)
                <div class="tab-pane fade show active" id="buyers-pane" role="tabpanel">
                    <div class="chat-list" id="buyers-sidebar" style="height: 500px; overflow-y: auto;">
                        @forelse($buyerConversations as $chat)
                        @php
                        $me = auth()->user();
                        $isSender = ($chat->sender_id == $me->id && $chat->sender_type == get_class($me));
                        $other = $isSender ? $chat->receiver : $chat->sender;
                        $otherName = $other->business_name ?? $other->name ?? $other->full_name ?? 'Buyer';
                        @endphp
                        <div class="chat-item p-3 border-bottom d-flex align-items-center {{ isset($activeId) && $activeId == $chat->id ? 'active' : '' }}" 
                             id="chat-item-{{ $chat->id }}"
                             onclick="openChat({{ $chat->id }}, '{{ addslashes($otherName) }}')">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">{{ $otherName }}</h6>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $chat->updated_at->diffForHumans(null, true) }}</small>
                                </div>
                                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">{{ $chat->messages->last()->body ?? 'Start conversation' }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center p-5 text-muted">No buyer conversations yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="tab-pane fade" id="craftsmen-pane" role="tabpanel">
                    <div class="chat-list" id="craftsmen-sidebar" style="height: 500px; overflow-y: auto;">
                        @forelse($craftsmanConversations as $chat)
                        @php
                        $me = auth()->user();
                        $isSender = ($chat->sender_id == $me->id && $chat->sender_type == get_class($me));
                        $other = $isSender ? $chat->receiver : $chat->sender;
                        $otherName = $other->business_name ?? $other->name ?? $other->full_name ?? 'Craftsman';
                        @endphp
                        <div class="chat-item p-3 border-bottom d-flex align-items-center {{ isset($activeId) && $activeId == $chat->id ? 'active' : '' }}" 
                             id="chat-item-{{ $chat->id }}"
                             onclick="openChat({{ $chat->id }}, '{{ addslashes($otherName) }}')">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">{{ $otherName }}</h6>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $chat->updated_at->diffForHumans(null, true) }}</small>
                                </div>
                                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">{{ $chat->messages->last()->body ?? 'Start conversation' }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center p-5 text-muted">No craftsman conversations yet.</div>
                        @endforelse
                    </div>
                </div>
                @endif

                @if($isSuperAdmin)
                <!-- Monitoring Pane -->
                <div class="tab-pane fade" id="monitoring-pane" role="tabpanel">
                    <div class="p-3 bg-purple-50 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-purple-700">Monitoring: <span id="monitored-admin-name"></span></h6>
                        <button class="btn btn-sm btn-link text-muted p-0" onclick="closeMonitoring()"><i class="bi bi-x-circle"></i></button>
                    </div>
                    
                    <div class="px-3 pt-2 bg-purple-50">
                        <ul class="nav nav-pills nav-fill mb-2 bg-white rounded-pill p-1 shadow-sm" id="monitorTabs" role="tablist" style="font-size: 0.7rem;">
                            <li class="nav-item">
                                <button class="nav-link active rounded-pill py-1 fw-bold" onclick="setMonitorFilter('buyer')">Buyers</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill py-1 fw-bold" onclick="setMonitorFilter('craftsman')">Craftsmen</button>
                            </li>
                        </ul>
                    </div>

                    <div class="chat-list" id="monitoring-sidebar" style="height: 400px; overflow-y: auto;">
                        @foreach($all_conversations as $chat)
                        @php
                        $sender = $chat->sender;
                        $receiver = $chat->receiver;
                        $sName = $sender->name ?? $sender->full_name ?? $sender->business_name ?? 'User';
                        $rName = $receiver->name ?? $receiver->full_name ?? $receiver->business_name ?? 'User';
                        @endphp
                        <div class="chat-item p-3 border-bottom d-flex align-items-center monitoring-item" 
                             id="monitor-item-{{ $chat->id }}" 
                             data-sender-id="{{ $chat->sender_id }}"
                             data-receiver-id="{{ $chat->receiver_id }}"
                             data-sender-type="{{ $chat->sender_type }}"
                             data-receiver-type="{{ $chat->receiver_type }}"
                             style="display: none;"
                             onclick="openChat({{ $chat->id }}, '{{ addslashes($sName) }} & {{ addslashes($rName) }}')">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold small text-truncate" style="max-width: 150px;">{{ $sName }} & {{ $rName }}</h6>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ $chat->updated_at->diffForHumans(null, true) }}</small>
                                </div>
                                <small class="text-muted text-truncate d-block" style="max-width: 200px; font-size: 0.7rem;">{{ $chat->messages->last()->body ?? 'View chat history' }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($isSuperAdmin)
                <div class="tab-pane fade" id="admins-pane" role="tabpanel">
                    <div class="chat-list" style="height: 500px; overflow-y: auto; padding: 15px;">
                        @foreach($admins as $admin)
                        <div class="mb-4">
                            <!-- Admin Category Header -->
                            <div class="d-flex align-items-center mb-2" style="cursor: default;">
                                <div class="admin-folder"><i class="bi bi-folder-fill"></i></div>
                                <h6 class="mb-0 fw-bold text-dark text-uppercase letter-spacing-1" style="font-size: 0.85rem;">{{ $admin->full_name }}</h6>
                                <span class="badge bg-purple-100 text-purple-700 ms-auto rounded-pill" style="font-size: 0.65rem;">{{ $admin->convo_count }}</span>
                            </div>

                            <div class="admin-tree-container">
                                <!-- Buyers Branch -->
                                @if($admin->buyer_convos->count() > 0)
                                <div class="admin-branch" data-bs-toggle="collapse" data-bs-target="#admin-{{ $admin->id }}-buyers">
                                    <i class="bi bi-person-lines-fill"></i> Buyers ({{ $admin->buyer_convos->count() }})
                                </div>
                                <div class="collapse show" id="admin-{{ $admin->id }}-buyers">
                                    @foreach($admin->buyer_convos as $chat)
                                        @php
                                        $isSender = ($chat->sender_id == $admin->id && $chat->sender_type == get_class($admin));
                                        $other = $isSender ? $chat->receiver : $chat->sender;
                                        $otherName = $other->business_name ?? $other->name ?? $other->full_name ?? 'Buyer';
                                        @endphp
                                        <div class="admin-branch py-1 small text-muted" onclick="openChat({{ $chat->id }}, '{{ addslashes($otherName) }} (via {{ addslashes($admin->full_name) }})')">
                                            <i class="bi bi-chat-text"></i> {{ $otherName }}
                                        </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="ps-4 py-1 small text-muted italic" style="font-size: 0.75rem;">No buyer chats</div>
                                @endif

                                <!-- Craftsmen Branch -->
                                @if($admin->craftsman_convos->count() > 0)
                                <div class="admin-branch" data-bs-toggle="collapse" data-bs-target="#admin-{{ $admin->id }}-crafts">
                                    <i class="bi bi-hammer"></i> Craftsmen ({{ $admin->craftsman_convos->count() }})
                                </div>
                                <div class="collapse show" id="admin-{{ $admin->id }}-crafts">
                                    @foreach($admin->craftsman_convos as $chat)
                                        @php
                                        $isSender = ($chat->sender_id == $admin->id && $chat->sender_type == get_class($admin));
                                        $other = $isSender ? $chat->receiver : $chat->sender;
                                        $otherName = $other->business_name ?? $other->name ?? $other->full_name ?? 'Craftsman';
                                        @endphp
                                        <div class="admin-branch py-1 small text-muted" onclick="openChat({{ $chat->id }}, '{{ addslashes($otherName) }} (via {{ addslashes($admin->full_name) }})')">
                                            <i class="bi bi-chat-text"></i> {{ $otherName }}
                                        </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="ps-4 py-1 small text-muted italic" style="font-size: 0.75rem;">No craftsman chats</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-8 col-lg-9 p-0 d-flex flex-column bg-white">
            <div id="chat-header" class="p-3 border-bottom d-none shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-purple-100 p-2 rounded-circle"><i class="bi bi-person-fill text-purple-600"></i></div>
                        <h6 class="mb-0 fw-bold text-uppercase" id="chat-user-name">Select a conversation</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="video-call-btn" class="btn btn-sm btn-outline-primary d-none" target="_blank">
                            <i class="bi bi-camera-video-fill me-1"></i> Video Call
                        </a>
                        @if($isSuperAdmin || $isAdmin)
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteConversation()">
                            <i class="bi bi-trash-fill me-1"></i> Delete Chat
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="message-area flex-grow-1 position-relative" id="message-container">
                <div class="message-spinner text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted small">Loading messages...</p>
                </div>
                <div class="m-auto text-center text-muted" id="placeholder">
                    <i class="bi bi-chat-dots-fill text-purple-200" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold text-slate-400">Your Inbox</h5>
                </div>
            </div>

            <div class="p-4 border-top bg-white d-none position-relative" id="input-area">
                <div id="voice-overlay" class="recording-overlay">
                    <div class="dot-pulse"></div>
                    <span id="timer" class="fw-bold text-danger">00:00</span>
                    <span class="text-muted ms-auto small">Recording... Release to send</span>
                </div>
                <div id="preview-box" class="mb-2 d-flex flex-wrap gap-2"></div>
                <form onsubmit="sendMessage(event)">
                    <div class="input-group gap-2 align-items-center">
                        <button type="button" class="btn btn-outline-secondary rounded-circle border-0" onclick="document.getElementById('file-input').click()"><i class="bi bi-paperclip"></i></button>
                        <input type="file" id="file-input" multiple class="d-none" onchange="previewFiles(this)">
                        <input type="text" id="msg-input" class="form-control rounded-pill px-4 shadow-none" placeholder="Type a message...">
                        <button type="button" class="btn btn-outline-secondary rounded-circle border-0" id="voice-trigger"><i class="bi bi-mic"></i></button>
                        <button class="btn btn-primary rounded-pill px-4" type="submit" style="background: #6d28d9; border: none;"><i class="bi bi-send-fill"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0 bg-light">
        <h5 class="modal-title fw-bold" id="newChatModalLabel">New Conversation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="user-search-input" class="form-control border-0 shadow-none px-2" placeholder="Search for a user..." onkeyup="searchUsers(this.value)">
        </div>
        <div id="search-results" class="list-group list-group-flush mb-3" style="max-height: 250px; overflow-y: auto;">
        </div>
        
        <h6 class="fw-bold text-muted small text-uppercase mb-2">Suggested Contacts</h6>
        <div class="list-group list-group-flush" id="suggested-contacts">
            @foreach($suggestedContacts as $contact)
                <a href="/{{ request()->segment(1) }}/chat/start/{{ $contact['id'] }}/{{ $contact['type'] }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded mb-1 bg-light">
                    <div class="bg-purple-100 p-2 rounded-circle"><i class="bi bi-person-fill text-purple-600"></i></div>
                    <div>
                        <h6 class="mb-0 fw-bold">{{ $contact['name'] }}</h6>
                    </div>
                </a>
            @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
    // Laravel Echo Configuration
    let activeId = null;
    let selectedFiles = [];
    let mediaRecorder;
    let audioChunks = [];
    let timerInterval;
    let seconds = 0;
    let isRecording = false;

    const prefix = "{{ request()->segment(1) }}";
    const myId = "{{ auth()->id() }}";
    const myType = "{!! addslashes(get_class(auth()->user())) !!}";
    const isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    let currentUnsubscribe = null;

    try {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') ?? env('REVERB_APP_KEY', 'lyohtqijqnxk7v0haqro') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.options.host') ?? env('REVERB_HOST', '127.0.0.1') }}',
            wsPort: {{ config('broadcasting.connections.reverb.options.port') ?? env('REVERB_PORT', 8080) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port') ?? env('REVERB_PORT', 8080) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
        });
    } catch (e) {
        console.error('Echo initialization error:', e);
    }

    function openChat(id, name) {
        // Leave previous Echo channel
        if (activeId) {
            window.Echo.leave(`chat.${activeId}`);
        }

        activeId = id;
        document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('active'));
        
        // Try to find either personal chat item or monitored chat item
        const item = document.getElementById(`chat-item-${id}`) || document.getElementById(`monitor-item-${id}`);
        if (item) item.classList.add('active');
        
        document.getElementById('chat-header').classList.remove('d-none');
        document.getElementById('input-area').classList.remove('d-none');
        document.getElementById('placeholder').classList.add('d-none');
        document.getElementById('chat-user-name').innerText = name;
        
        // Show Video Call button and set link
        const videoBtn = document.getElementById('video-call-btn');
        if (videoBtn) {
            videoBtn.classList.remove('d-none');
            videoBtn.href = `/${prefix}/meetings/chat_${id}`;
        }
        
        loadMessages(id, true);

        // --- Laravel Echo Listener ---
        if (window.Echo) {
            window.Echo.private(`conversation.${id}`)
                .listen('.message.sent', (e) => {
                    const myTypeBasename = myType.split('\\').pop();
                    const isMe = (e.sender_id == myId && e.sender_type === myTypeBasename);
                    
                    // Only append if it's from the other person
                    if (!isMe) {
                        const container = document.getElementById('message-container');
                        const bubbleHtml = `
                        <div class="chat-bubble bubble-other" id="message-${e.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">${e.body || ''}</div>
                            </div>
                            <div style="font-size:0.65rem; opacity:0.8; margin-top:8px;" class="text-end d-flex align-items-center justify-content-end gap-2">
                                <span>${new Date(e.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>
                            </div>
                        </div>`;
                        const placeholder = document.getElementById('placeholder');
                        if (placeholder) placeholder.classList.add('d-none');
                        
                        container.insertAdjacentHTML('beforeend', bubbleHtml);
                        container.scrollTop = container.scrollHeight;

                        // Mark as delivered immediately
                        fetch(`{{ route('chat.message.delivered') }}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ message_ids: [e.id] })
                        });

                        if (document.visibilityState === 'visible') {
                            fetch(`{{ route('chat.message.read') }}`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ message_ids: [e.id] })
                            });
                        }
                    }
                })
                .listen('.message.delivered', (e) => {
                    const tickEl = document.getElementById(`tick-${e.messageId}`);
                    if (tickEl && !tickEl.classList.contains('read-tick')) {
                        tickEl.innerHTML = '<i class="bi bi-check2-all" style="color: #6c757d; font-size: 0.9rem;"></i>';
                    }
                })
                .listen('.message.read', (e) => {
                    const tickEl = document.getElementById(`tick-${e.messageId}`);
                    if (tickEl) {
                        tickEl.innerHTML = '<i class="bi bi-check2-all" style="color: #00f2fe; font-size: 0.9rem;"></i>';
                        tickEl.classList.add('read-tick');
                    }
                })
                .listen('.message.deleted', (e) => {
                    const el = document.getElementById(`message-${e.messageId}`);
                    if (el) el.remove();
                });
        }
        // -----------------------------------
    }

    function startTimer() {
        seconds = 0;
        document.getElementById('timer').innerText = "00:00";
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            seconds++;
            let mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            let secs = (seconds % 60).toString().padStart(2, '0');
            document.getElementById('timer').innerText = `${mins}:${secs}`;
        }, 1000);
    }

    const voiceBtn = document.getElementById('voice-trigger');

    let isProcessingAudio = false; // New flag to prevent sending before file is ready

async function startRecording() {
    if (isRecording) return;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus') 
                         ? 'audio/webm;codecs=opus' 
                         : 'audio/webm';
        
        mediaRecorder = new MediaRecorder(stream, { mimeType });
        audioChunks = [];
        isRecording = true;
        isProcessingAudio = true; // Block sending until finished

        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(audioChunks, { type: mediaRecorder.mimeType });
            const extension = mediaRecorder.mimeType.includes('ogg') ? 'ogg' : 'webm';
            const fileName = `voice_${Date.now()}.${extension}`;
            const file = new File([blob], fileName, { type: mediaRecorder.mimeType });
            
            selectedFiles.push(file);
            isProcessingAudio = false; // Now it is safe to send

            const durationText = document.getElementById('timer').innerText;
            document.getElementById('preview-box').insertAdjacentHTML('beforeend', `
                <span class="badge bg-danger p-2 d-flex align-items-center gap-2" id="preview-${fileName}">
                    <i class="bi bi-mic-fill"></i> Voice (${durationText})
                    <i class="bi bi-x-circle cursor-pointer" onclick="removeFile('${fileName}')"></i>
                </span>`);
        };

        mediaRecorder.start(); // Remove timeslice - sometimes helps with metadata
        document.getElementById('input-area').classList.add('recording-active');
        startTimer();
    } catch (err) {
        alert("Microphone error.");
        isRecording = false;
        isProcessingAudio = false;
    }
}

// Update the Send function to wait for the audio
async function sendMessage(e) {
    e.preventDefault();
    
    // 1. Wait if audio is still being processed
    if (isProcessingAudio) {
        alert("Please wait for the recording to finish processing...");
        return;
    }

    const input = document.getElementById('msg-input');
    const sendBtn = e.target.querySelector('button[type="submit"]');
    const originalBtnHtml = sendBtn.innerHTML;

    if (!input.value.trim() && selectedFiles.length === 0) return;
    
    // Show loading state
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

    const formData = new FormData();
    formData.append('conversation_id', activeId);
    formData.append('body', input.value.trim());
    selectedFiles.forEach((f, i) => formData.append(`attachments[${i}]`, f));

    // Clear UI immediately to prevent double-send
    input.value = '';
    selectedFiles = [];
    document.getElementById('preview-box').innerHTML = '';

    await fetch(`/${prefix}/chat/send`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    });
    
    sendBtn.disabled = false;
    sendBtn.innerHTML = originalBtnHtml;

    loadMessages(activeId, true);
}

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(t => t.stop());
            clearInterval(timerInterval);
            document.getElementById('input-area').classList.remove('recording-active');
            isRecording = false;
        }
    }

    // Voice Event Listeners
    voiceBtn.addEventListener('mousedown', startRecording);
    window.addEventListener('mouseup', stopRecording);
    
    voiceBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        startRecording();
    });
    window.addEventListener('touchend', stopRecording);

    function removeFile(name) {
        selectedFiles = selectedFiles.filter(f => f.name !== name);
        const el = document.getElementById(`preview-${name}`);
        if(el) el.remove();
    }

    function previewFiles(input) {
        const files = Array.from(input.files);
        files.forEach(file => {
            if (file.size > 10 * 1024 * 1024) return alert('Max 10MB');
            selectedFiles.push(file);
            const shortName = file.name.substring(0,10);
            document.getElementById('preview-box').insertAdjacentHTML('beforeend', `
                <span class="badge bg-light text-dark p-2 border" id="preview-${file.name}">
                    ${shortName}... <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="removeFile('${file.name}')"></i>
                </span>`);
        });
    }

    

    async function toggleVoice(id) {
        const audio = document.getElementById(`audio-${id}`);
        const icon = document.getElementById(`icon-${id}`);
        
        // Force duration refresh on first play
        if (audio.currentTime === 0 && isFinite(audio.duration)) {
            audio.currentTime = audio.duration - 0.1;
            setTimeout(() => { audio.currentTime = 0; }, 50);
        }

        // Pause all other playing audios
        document.querySelectorAll('audio').forEach(a => {
            if (a !== audio) {
                a.pause();
                const otherId = a.id.replace('audio-', '');
                const otherIcon = document.getElementById(`icon-${otherId}`);
                if (otherIcon) otherIcon.className = 'bi bi-play-fill';
            }
        });

        if (audio.paused) {
            try {
                await audio.play();
                icon.className = 'bi bi-pause-fill';
            } catch (e) { 
                console.error("Playback failed", e); 
                setTimeout(() => { audio.play(); icon.className = 'bi bi-pause-fill'; }, 100);
            }
        } else {
            audio.pause();
            icon.className = 'bi bi-play-fill';
        }
    }



    function updateProgress(id) {
        const audio = document.getElementById(`audio-${id}`);
        const progress = document.getElementById(`progress-${id}`);
        if (audio.duration) {
            const percent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = percent + '%';
        }
    }

    function resetVoice(id) {
        const icon = document.getElementById(`icon-${id}`);
        const progress = document.getElementById(`progress-${id}`);
        icon.className = 'bi bi-play-fill';
        progress.style.width = '0%';
    }

    async function loadMessages(id, scroll) {
        const container = document.getElementById('message-container');
        container.classList.add('loading-messages');
        try {
            const res = await fetch(`/${prefix}/chat/${id}`);
            const data = await res.json();
            const container = document.getElementById('message-container');
            let html = '';
            data.messages.forEach(msg => {
                const isMe = (msg.sender_id == myId && msg.sender_type === myType);
                let attachHtml = '';
                msg.attachments.forEach(at => {
                    if (at.file_type === 'image') {
                        attachHtml += `<img src="${at.url}" class="img-fluid rounded mt-2 shadow-sm" style="max-width:250px" onclick="window.open(this.src)">`;
                    } else if (at.file_type === 'voice') {
                        const voiceId = `voice-${at.id}`;
                        const audioSrc = at.base64_data || at.url;
                        attachHtml += `
                            <div class="custom-voice-player" id="player-${voiceId}">
                                <div class="play-btn" onclick="toggleVoice('${voiceId}')" id="btn-${voiceId}">
                                    <i class="bi bi-play-fill" id="icon-${voiceId}"></i>
                                </div>
                                <div class="voice-progress">
                                    <div class="voice-progress-fill" id="progress-${voiceId}"></div>
                                </div>
                                <a href="${at.url}" download="${at.file_name}" class="voice-download-link" title="Download Voice">
                                    <i class="bi bi-cloud-arrow-down-fill"></i>
                                </a>
                                <audio id="audio-${voiceId}" ontimeupdate="updateProgress('${voiceId}')" onended="resetVoice('${voiceId}')" preload="auto">
                                    <source src="${audioSrc}" type="${at.mime_type || 'audio/webm'}">
                                </audio>
                            </div>`;
                    } else {
                        attachHtml += `<div class="mt-2"><a href="${at.url}" target="_blank" class="btn btn-sm btn-light border w-100 text-start small"><i class="bi bi-file-earmark"></i> ${at.file_name}</a></div>`;
                    }
                });
                const deleteBtn = (isSuperAdmin || isAdmin) ? `<i class="bi bi-trash cursor-pointer ms-2" style="font-size: 0.8rem; opacity: 0.5; color: ${isMe ? 'white' : 'red'}" onclick="deleteMessage(${msg.id})"></i>` : '';
                
                let tickHtml = '';
                if (isMe) {
                    let isRead = msg.statuses && msg.statuses.some(s => s.read_at);
                    let isDelivered = msg.statuses && msg.statuses.some(s => s.delivered_at);
                    if (isRead) {
                        tickHtml = `<span id="tick-${msg.id}" class="read-tick"><i class="bi bi-check2-all" style="color: #00f2fe; font-size: 0.9rem;"></i></span>`;
                    } else if (isDelivered) {
                        tickHtml = `<span id="tick-${msg.id}"><i class="bi bi-check2-all" style="color: #6c757d; font-size: 0.9rem;"></i></span>`;
                    } else {
                        tickHtml = `<span id="tick-${msg.id}"><i class="bi bi-check2" style="color: #6c757d; font-size: 0.9rem;"></i></span>`;
                    }
                }

                html += `
                <div class="chat-bubble ${isMe ? 'bubble-me' : 'bubble-other'}" id="message-${msg.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">${msg.body || ''}</div>
                        ${deleteBtn}
                    </div>
                    ${attachHtml}
                    <div style="font-size:0.65rem; opacity:0.8; margin-top:8px;" class="text-end d-flex align-items-center justify-content-end gap-2">
                        <span>${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>
                        ${tickHtml}
                    </div>
                </div>`;
            });
            container.innerHTML = html;
            if (scroll) container.scrollTop = container.scrollHeight;
        } catch (e) { 
            console.error("Error loading messages", e); 
        } finally {
            container.classList.remove('loading-messages');
        }
    }

    async function deleteMessage(id) {
        if (!confirm('Are you sure you want to delete this message?')) return;
        try {
            const res = await fetch(`/${prefix}/chat/message/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            if (res.ok) {
                const el = document.getElementById(`message-${id}`);
                if (el) el.remove();
            } else {
                const data = await res.json();
                alert(data.message || 'Error deleting message');
            }
        } catch (e) { console.error('Delete error', e); }
    }

    async function deleteConversation() {
        if (!activeId) return;
        if (!confirm('Are you sure you want to delete the ENTIRE conversation? This cannot be undone.')) return;
        try {
            const res = await fetch(`/${prefix}/chat/conversation/${activeId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            if (res.ok) {
                location.reload(); 
            } else {
                const data = await res.json();
                alert(data.message || 'Error deleting conversation');
            }
        } catch (e) { console.error('Delete error', e); }
    }

    let currentMonitoredAdminId = null;
    let monitorTypeFilter = 'buyer';

    function filterByAdmin(adminId, adminName) {
        currentMonitoredAdminId = adminId;
        document.getElementById('monitored-admin-name').innerText = adminName;
        applyMonitorFilter();
        
        // Switch to monitoring tab
        document.getElementById('convos-pane').classList.remove('show', 'active');
        document.getElementById('admins-pane').classList.remove('show', 'active');
        document.getElementById('monitoring-pane').classList.add('show', 'active');
    }

    function setMonitorFilter(type) {
        monitorTypeFilter = type;
        // Update tab UI
        document.querySelectorAll('#monitorTabs .nav-link').forEach(btn => {
            if (btn.innerText.toLowerCase().includes(type)) btn.classList.add('active');
            else btn.classList.remove('active');
        });
        applyMonitorFilter();
    }

    function applyMonitorFilter() {
        if (!currentMonitoredAdminId) return;

        const buyerType = "App\\Models\\Buyer";
        const craftsmanType = "App\\Models\\Craftman";
        const adminType = "App\\Models\\ProcessOwner";
        
        const targetType = monitorTypeFilter === 'buyer' ? buyerType : craftsmanType;

        document.querySelectorAll('#monitoring-sidebar .monitoring-item').forEach(item => {
            const senderId = item.getAttribute('data-sender-id');
            const receiverId = item.getAttribute('data-receiver-id');
            const senderType = item.getAttribute('data-sender-type');
            const receiverType = item.getAttribute('data-receiver-type');
            
            // Fix ID collision: Must check both ID and Type
            const isAdminInvolved = (senderId == currentMonitoredAdminId && senderType == adminType) || 
                                    (receiverId == currentMonitoredAdminId && receiverType == adminType);
            
            const isMeInvolved = (senderId == myId && senderType == myType) || (receiverId == myId && receiverType == myType);
            
            // Check if the other party is of the selected type (Buyer or Craftsman)
            const isOtherPartyCorrect = (senderType == targetType || receiverType == targetType);
            
            if (isAdminInvolved && !isMeInvolved && isOtherPartyCorrect) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function closeMonitoring() {
        currentMonitoredAdminId = null;
        document.getElementById('monitoring-pane').classList.remove('show', 'active');
        document.getElementById('convos-pane').classList.add('show', 'active');
        
        const triggerEl = document.querySelector('#convos-tab');
        if(triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();
    }

    function pollSidebar() {
        fetch(window.location.href).then(res => res.text()).then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update all relevant sidebar lists
            ['chat-sidebar', 'buyers-sidebar', 'craftsmen-sidebar', 'monitoring-sidebar', 'admins-pane'].forEach(id => {
                const newList = doc.getElementById(id);
                const oldList = document.getElementById(id);
                if (newList && oldList) {
                    oldList.innerHTML = newList.innerHTML;
                }
            });

            // Re-apply monitoring filter if active
            if (typeof applyMonitorFilter === 'function') {
                applyMonitorFilter();
            }

            if (activeId) {
                const activeItem = document.getElementById(`chat-item-${activeId}`);
                if (activeItem) activeItem.classList.add('active');
            }
        });
    }

    // Use websockets exclusively instead of polling
    // Removed polling setInterval

    async function searchUsers(query) {
        if (query.length < 2) {
            document.getElementById('search-results').innerHTML = '';
            return;
        }
        try {
            const res = await fetch(`/${prefix}/chat/search?q=${encodeURIComponent(query)}`);
            if (res.ok) {
                const users = await res.json();
                let html = '';
                users.forEach(u => {
                    html += `
                    <a href="/${prefix}/chat/start/${u.id}/${u.type}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded mb-1 bg-light">
                        <div class="bg-purple-100 p-2 rounded-circle"><i class="bi bi-person-fill text-purple-600"></i></div>
                        <h6 class="mb-0 fw-bold">${u.name}</h6>
                    </a>`;
                });
                document.getElementById('search-results').innerHTML = html;
            }
        } catch (e) { console.error('Search error', e); }
    }
</script>
@endsection