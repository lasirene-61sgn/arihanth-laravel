@extends('craftsman.layouts.app')

@section('title', 'Chat')

@section('content')
<style>
    .chat-wrap { height: calc(100vh - 80px); display:flex; gap:0; border-radius:16px; overflow:hidden; box-shadow:0 4px 32px rgba(5,150,105,0.1); border:1px solid #a7f3d0; }
    .chat-sidebar { width:320px; min-width:280px; background:#fff; border-right:1px solid #d1fae5; display:flex; flex-direction:column; }
    .chat-sidebar-header { padding:18px 20px 14px; border-bottom:1px solid #d1fae5; background:linear-gradient(135deg,#065f46 0%,#059669 100%); }
    .convo-list { flex:1; overflow-y:auto; }
    .convo-item { display:block; padding:12px 16px; border-bottom:1px solid #f0fdf4; text-decoration:none; color:inherit; transition:background 0.15s; cursor:pointer; position:relative; }
    .convo-item:hover { background:#f0fdf4; }
    .convo-item.active { background:#d1fae5; border-left:4px solid #059669; }
    .convo-name { font-size:0.88rem; color:#064e3b; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:170px; }
    .convo-time { font-size:0.7rem; color:#6ee7b7; white-space:nowrap; }
    .convo-preview { font-size:0.78rem; color:#059669; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .unread-badge { background:#059669; color:#fff; font-size:0.65rem; font-weight:800; min-width:18px; height:18px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0 4px; }
    .chat-area { flex:1; display:flex; flex-direction:column; background:#f0fdf4; }
    .chat-header { padding:14px 20px; background:#fff; border-bottom:1px solid #d1fae5; display:flex; align-items:center; justify-content:space-between; }
    .msg-list { flex:1; overflow-y:auto; padding:20px 24px; display:flex; flex-direction:column; gap:6px; }
    .chat-input-area { padding:12px 16px; background:#fff; border-top:1px solid #d1fae5; }
    .msg-bubble { max-width:68%; padding:9px 14px; border-radius:16px; word-wrap:break-word; position:relative; font-size:0.88rem; }
    .msg-outgoing { background:linear-gradient(135deg,#065f46,#059669); color:#fff; margin-left:auto; border-bottom-right-radius:4px; }
    .msg-incoming { background:#fff; color:#064e3b; margin-right:auto; border-bottom-left-radius:4px; box-shadow:0 1px 4px rgba(5,150,105,0.1); }
    .msg-time { font-size:0.67rem; opacity:0.75; margin-top:4px; display:flex; justify-content:flex-end; align-items:center; gap:3px; }
    .msg-tick { font-size:0.8rem; }
    .msg-tick.read { color:#53bdeb; }
    .attach-preview { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
    .attach-preview-item { position:relative; }
    .attach-preview-item img { width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #a7f3d0; }
    .attach-preview-item .remove-file { position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:18px; height:18px; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .file-doc-chip { background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:4px 10px; font-size:0.75rem; color:#059669; display:flex; align-items:center; gap:5px; }
    .input-row { display:flex; gap:8px; align-items:flex-end; }
    .attach-btn { width:38px; height:38px; border-radius:50%; background:#d1fae5; border:none; color:#059669; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .attach-btn:hover { background:#a7f3d0; }
    .msg-input { flex:1; border:1.5px solid #a7f3d0; border-radius:22px; padding:9px 16px; font-size:0.9rem; outline:none; background:#f0fdf4; resize:none; max-height:120px; min-height:38px; }
    .send-btn { background:linear-gradient(135deg,#065f46,#059669); color:#fff; border:none; border-radius:22px; padding:9px 22px; font-weight:700; cursor:pointer; }
    .send-btn:disabled { opacity:0.5; cursor:default; }
    .msg-attachment { margin-top:6px; }
    .msg-attachment img { max-width:220px; border-radius:10px; display:block; }
    .msg-attachment audio { display:block; margin-top:4px; width:200px; }
    .msg-attachment .doc-link { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.2); border-radius:8px; padding:6px 12px; font-size:0.8rem; color:inherit; text-decoration:none; }
</style>

<div class="chat-wrap">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <h5 style="margin:0;color:#fff;font-weight:800;font-size:1rem;">💬 Messages</h5>
                    <small style="color:#a7f3d0;font-size:0.75rem;">{{ $user->business_name ?? $user->name }}</small>
                </div>
                <button type="button" class="attach-btn" style="background:rgba(255,255,255,0.2);color:#fff;" data-bs-toggle="modal" data-bs-target="#newCraftsmanChatModal" title="New Message">+</button>
            </div>
        </div>
        <div class="convo-list" id="craftsman-convo-list">
            @forelse($conversations as $convo)
                @php
                    $otherUser = ($convo->sender_id == $user->id && $convo->sender_type == get_class($user)) ? $convo->receiver : $convo->sender;
                    $lastMsg = $convo->messages->first();
                    $unread = $convo->unread_count ?? 0;
                @endphp
                <a href="javascript:void(0)" class="convo-item" data-id="{{ $convo->id }}"
                   data-title="{{ $otherUser->name ?? $otherUser->full_name ?? ('Conversation #'.$convo->id) }}"
                   onclick="window.loadConversation({{ (int)$convo->id }}, this.getAttribute('data-title'))">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                        <span class="convo-name">{{ $otherUser->name ?? $otherUser->full_name ?? ('Conversation #'.$convo->id) }}</span>
                        <div style="display:flex;align-items:center;gap:5px;">
                            <span class="convo-time">{{ $convo->updated_at ? $convo->updated_at->diffForHumans(null,true) : '' }}</span>
                            <span class="unread-badge" id="badge-{{ $convo->id }}" style="{{ $unread > 0 ? '' : 'display:none;' }}">{{ $unread ?: '0' }}</span>
                        </div>
                    </div>
                    <span class="convo-preview" id="preview-{{ $convo->id }}">{{ $lastMsg->body ?? 'Click to open conversation' }}</span>
                </a>
            @empty
                <div style="padding:40px 20px;text-align:center;color:#6ee7b7;">
                    <p style="margin-bottom:8px;font-size:0.9rem;">No conversations yet.</p>
                    <button class="btn btn-sm" style="background:#059669;color:#fff;border-radius:8px;" data-bs-toggle="modal" data-bs-target="#newCraftsmanChatModal">Start Chat</button>
                </div>
            @endforelse
        </div>
    </div>

    <div class="chat-area">
        <div class="chat-header">
            <div>
                <h6 id="chat-title" style="margin:0;font-weight:800;color:#064e3b;font-size:1rem;">Select a conversation</h6>
                <small id="chat-subtitle" style="color:#6ee7b7;">No active chat</small>
            </div>
        </div>
        <div class="msg-list" id="chat-messages">
            <div style="margin:auto;text-align:center;color:#6ee7b7;">
                <div style="font-size:2.5rem;margin-bottom:8px;">💬</div>
                <p>Select a conversation or start a new chat.</p>
            </div>
        </div>
        <div class="chat-input-area">
            <div class="attach-preview" id="attach-preview"></div>
            <form id="chat-form" onsubmit="window.handleSendMessage(event)">
                <input type="hidden" id="active_conversation_id" value="">
                <input type="file" id="file-input" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.mp3,.webm,.ogg" style="display:none;" onchange="handleFileSelect(this)">
                <div class="input-row">
                    <button type="button" class="attach-btn" onclick="document.getElementById('file-input').click()" title="Attach file">📎</button>
                    <textarea class="msg-input" id="message-input" placeholder="Type a message..." autocomplete="off" rows="1" disabled
                        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();window.handleSendMessage(event);}"></textarea>
                    <button type="submit" class="send-btn" id="send-btn" disabled>Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="newCraftsmanChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 40px rgba(5,150,105,0.15);">
            <div class="modal-header" style="background:linear-gradient(135deg,#065f46,#059669);border-radius:16px 16px 0 0;border:none;">
                <h5 class="modal-title" style="color:#fff;font-weight:800;">Start New Chat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div style="padding:16px;border-bottom:1px solid #d1fae5;">
                    <input type="text" id="chat-search-input" class="form-control" style="border-radius:10px;border:1.5px solid #a7f3d0;"
                        placeholder="Search admins or your staff..." oninput="searchChatUsers(this.value)">
                </div>
                <div id="chat-search-results" style="max-height:350px;overflow-y:auto;">
                    @if(isset($suggestedContacts) && count($suggestedContacts) > 0)
                        @foreach($suggestedContacts as $contact)
                            @php $c = is_array($contact) ? (object)$contact : $contact; @endphp
                            <a href="javascript:void(0)" onclick="startChatAjax('{{ route('craftsman.chat.start', ['receiverId'=>$c->id,'type'=>$c->type]) }}','{{ addslashes($c->name) }}')"
                               style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f0fdf4;text-decoration:none;color:inherit;transition:background 0.15s;"
                               onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''">
                                <div>
                                    <div style="font-weight:700;color:#064e3b;font-size:0.88rem;">{{ $c->name }}</div>
                                    <small style="color:#6ee7b7;">{{ $c->code ?? '' }}</small>
                                </div>
                                <span style="background:#059669;color:#fff;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;">Chat</span>
                            </a>
                        @endforeach
                    @else
                        <div style="padding:32px;text-align:center;color:#6ee7b7;">No contacts available.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    const authUserId   = {{ (int)$user->id }};
    const authUserType = "{{ addslashes(get_class($user)) }}";
    const sendRouteUrl     = "{{ route('craftsman.chat.send') }}";
    const showRouteBaseUrl = "{{ url('craftsman/chat') }}";
    const searchRouteUrl   = "{{ route('craftsman.chat.search') }}";
    const markDeliveredUrl = "{{ route('chat.message.delivered') }}";
    const markReadUrl      = "{{ route('chat.message.read') }}";
    const csrfToken        = "{{ csrf_token() }}";

    let activeConversationId = null;
    let unreadMessageIds = [];
    let selectedFiles = [];

    window.Pusher = Pusher;
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost: '{{ config("broadcasting.connections.reverb.options.host","localhost") }}',
            wsPort: {{ (int)config("broadcasting.connections.reverb.options.port",8080) }},
            wssPort: {{ (int)config("broadcasting.connections.reverb.options.port",8080) }},
            forceTLS: false,
            enabledTransports: ['ws','wss'],
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrfToken } }
        });
    } catch(e) { console.error("Echo init error:", e); }

    window.startChatAjax = function(url, titleName) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const modalEl = document.getElementById('newCraftsmanChatModal');
                if (modalEl) { const mi = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); mi.hide(); }
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');

                if (data.conversation_id) {
                    if (!document.querySelector(`.convo-item[data-id="${data.conversation_id}"]`)) {
                        const list = document.getElementById('craftsman-convo-list');
                        const link = document.createElement('a');
                        link.href = 'javascript:void(0)';
                        link.className = 'convo-item';
                        link.dataset.id = data.conversation_id;
                        link.dataset.title = titleName;
                        link.onclick = () => window.loadConversation(data.conversation_id, titleName);
                        link.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                            <span class="convo-name">${titleName}</span>
                            <div style="display:flex;align-items:center;gap:5px;">
                                <span class="convo-time">Just now</span>
                                <span class="unread-badge" id="badge-${data.conversation_id}" style="display:none;">0</span>
                            </div></div>
                            <span class="convo-preview" id="preview-${data.conversation_id}">Click to open conversation</span>`;
                        list.insertBefore(link, list.firstChild);
                    }
                    window.loadConversation(data.conversation_id, titleName);
                }
            }).catch(err => console.error(err));
    };

    window.loadConversation = function(conversationId, titleName) {
        activeConversationId = conversationId;
        document.getElementById('active_conversation_id').value = conversationId;
        const msgInput = document.getElementById('message-input');
        const sendBtn  = document.getElementById('send-btn');
        msgInput.disabled = false; msgInput.focus();
        sendBtn.disabled  = false;
        document.getElementById('chat-title').innerText    = titleName;
        document.getElementById('chat-subtitle').innerText = 'Connected in real-time';
        document.querySelectorAll('.convo-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.querySelector(`.convo-item[data-id="${conversationId}"]`);
        if (activeItem) activeItem.classList.add('active');
        const badge = document.getElementById(`badge-${conversationId}`);
        if (badge) { badge.innerText = '0'; badge.style.display = 'none'; }

        const chatBox = document.getElementById('chat-messages');
        chatBox.innerHTML = '<div style="margin:auto;text-align:center;color:#6ee7b7;">Loading messages...</div>';
        fetch(`${showRouteBaseUrl}/${conversationId}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                chatBox.innerHTML = '';
                if (data.messages && data.messages.length > 0) data.messages.forEach(msg => appendMessage(msg));
                else chatBox.innerHTML = '<div style="margin:auto;text-align:center;color:#6ee7b7;">No messages yet. Say hello! 👋</div>';
                scrollToBottom();
            }).catch(() => { chatBox.innerHTML = '<div style="margin:auto;text-align:center;color:red;">Error loading messages.</div>'; });
    };

    window.attachConversationListener = function(cId) {
        if (!window.Echo) return;
        window.Echo.private(`conversation.${cId}`)
            .listen('.message.sent', (e) => {
                if (activeConversationId == cId) {
                    appendMessage(e); scrollToBottom();
                    markAsDelivered([e.id]);
                    if (document.visibilityState === 'visible') markAsRead([e.id]);
                    else unreadMessageIds.push(e.id);
                } else {
                    const badge = document.getElementById(`badge-${cId}`);
                    if (badge) { let c = parseInt(badge.innerText)||0; c++; badge.innerText=c; badge.style.display='inline-flex'; }
                }
                moveConversationToTop(cId, e.body || '📎 Attachment', 'Just now');
            })
            .listen('.message.deleted', (e) => { 
                if (activeConversationId == cId) {
                    const el = document.getElementById(`msg-${e.messageId}`); if (el) el.remove(); 
                }
            });
    };

    @foreach($conversations as $convo)
        attachConversationListener({{ (int)$convo->id }});
    @endforeach

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && activeConversationId && unreadMessageIds.length > 0) { markAsRead(unreadMessageIds); unreadMessageIds = []; }
    });

    function markAsDelivered(ids) { fetch(markDeliveredUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken}, body:JSON.stringify({message_ids:ids}) }); }
    function markAsRead(ids) { fetch(markReadUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken}, body:JSON.stringify({message_ids:ids}) }); }

    // Listen to Global User Channel for NEW conversations
    (function() {
        const userChannelType = "{{ strtolower(class_basename(get_class($user))) }}";
        if (window.Echo) {
            window.Echo.private(`user.${userChannelType}.${authUserId}`)
                .listen('.message.sent', (e) => {
                    const list = document.getElementById('craftsman-convo-list');
                    if (list && !list.querySelector(`.convo-item[data-id="${e.conversation_id}"]`)) {
                        window.location.reload();
                    }
                });
        }
    })();

    function handleFileSelect(input) {
        Array.from(input.files).forEach(file => {
            const mb = file.size/1024/1024;
            const limit = file.type.startsWith('image/') ? 10 : 25;
            if (mb > limit) { alert(`${file.name} exceeds ${limit}MB limit.`); return; }
            selectedFiles.push(file);
        });
        input.value = '';
        renderAttachPreview();
    }

    function renderAttachPreview() {
        const box = document.getElementById('attach-preview');
        box.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = 'attach-preview-item';
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img'); img.src = URL.createObjectURL(file); item.appendChild(img);
            } else {
                const chip = document.createElement('div'); chip.className = 'file-doc-chip';
                chip.innerHTML = `📄 ${file.name.substring(0,20)}${file.name.length>20?'…':''}`; item.appendChild(chip);
            }
            const rm = document.createElement('button'); rm.className = 'remove-file'; rm.innerHTML = '×';
            rm.onclick = () => { selectedFiles.splice(idx,1); renderAttachPreview(); }; item.appendChild(rm);
            box.appendChild(item);
        });
    }

    window.handleSendMessage = function(e) {
        if (e && e.preventDefault) e.preventDefault();
        const input = document.getElementById('message-input');
        const text = input.value.trim();
        const convoId = document.getElementById('active_conversation_id').value;
        if (!convoId || (!text && selectedFiles.length === 0)) return;
        input.value = '';
        const fd = new FormData();
        fd.append('conversation_id', convoId);
        fd.append('body', text);
        selectedFiles.forEach((f,i) => fd.append(`attachments[${i}]`, f));
        selectedFiles = []; renderAttachPreview();
        const socketId = (window.Echo && window.Echo.socketId) ? window.Echo.socketId() : '';
        fetch(sendRouteUrl, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Socket-ID':socketId}, body:fd })
            .then(r => r.json())
            .then(msg => {
                appendMessage(msg);
                scrollToBottom();
                moveConversationToTop(convoId, msg.body||'📎 Attachment','Just now');
            }).catch(err => console.error(err));
    };

    function moveConversationToTop(convoId, lastMsg, timeText) {
        const list = document.getElementById('craftsman-convo-list');
        if (!list) return;
        const item = list.querySelector(`.convo-item[data-id="${convoId}"]`);
        if (item) {
            const preview = document.getElementById(`preview-${convoId}`);
            if (preview && lastMsg) preview.innerText = lastMsg;
            list.insertBefore(item, list.firstChild);
        }
    }

    function appendMessage(msg) {
        const chatBox = document.getElementById('chat-messages');
        if (!chatBox) return;
        const placeholder = chatBox.querySelector('[style*="margin:auto"]');
        if (placeholder) placeholder.remove();
        if (msg.id && document.getElementById(`msg-${msg.id}`)) return;
        const isMe = (parseInt(msg.sender_id)===parseInt(authUserId)) && (!msg.sender_type||msg.sender_type===authUserType);
        let tickHtml = '';
        if (isMe) {
            const isRead = (msg.statuses||[]).some(s => s.read_at);
            const isDlvr = (msg.statuses||[]).some(s => s.delivered_at);
            if (isRead) tickHtml=`<span class="msg-tick read" id="tick-${msg.id}">✓✓</span>`;
            else if (isDlvr) tickHtml=`<span class="msg-tick" id="tick-${msg.id}">✓✓</span>`;
            else tickHtml=`<span class="msg-tick" id="tick-${msg.id}">✓</span>`;
        } else { if (!msg.is_read) unreadMessageIds.push(msg.id); }

        let attHtml = '';
        if (msg.attachments && msg.attachments.length > 0) {
            msg.attachments.forEach(at => {
                const url = at.url || `/storage/${at.file_path}`;
                if (at.file_type==='image') attHtml+=`<div class="msg-attachment"><img src="${url}" alt="${at.file_name}" onclick="window.open('${url}');" style="cursor:pointer;"></div>`;
                else if (at.file_type==='voice') attHtml+=`<div class="msg-attachment"><audio controls src="${at.base64_data||url}"></audio></div>`;
                else attHtml+=`<div class="msg-attachment"><a class="doc-link" href="${url}" target="_blank">📄 ${at.file_name}</a></div>`;
            });
        }

        const time = msg.created_at ? (typeof msg.created_at==='string'&&msg.created_at.includes('T') ? new Date(msg.created_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}) : msg.created_at) : '';
        const bubble = document.createElement('div');
        if (msg.id) bubble.id = `msg-${msg.id}`;
        bubble.className = `msg-bubble ${isMe?'msg-outgoing':'msg-incoming'}`;
        bubble.innerHTML = `<div style="font-size:0.72rem;font-weight:700;margin-bottom:2px;opacity:0.85;">${isMe?'You':(msg.sender_name||'Admin')}</div>
            ${msg.body?`<div>${msg.body}</div>`:''}${attHtml}
            <div class="msg-time"><span>${time}</span>${tickHtml}</div>`;
        chatBox.appendChild(bubble);
    }

    function searchChatUsers(query) {
        const resultsBox = document.getElementById('chat-search-results');
        fetch(`${searchRouteUrl}?q=${encodeURIComponent(query)}`).then(r => r.json()).then(data => {
            resultsBox.innerHTML = '';
            if (data.length > 0) {
                data.forEach(contact => {
                    const url = `{{ url('craftsman/chat/start') }}/${contact.id}/${contact.type}`;
                    resultsBox.innerHTML += `<a href="javascript:void(0)" onclick="startChatAjax('${url}','${contact.name.replace(/'/g,"\\'")}') "
                        style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f0fdf4;text-decoration:none;color:inherit;">
                        <div><div style="font-weight:700;color:#064e3b;font-size:0.88rem;">${contact.name}</div><small style="color:#6ee7b7;">${contact.code??''}</small></div>
                        <span style="background:#059669;color:#fff;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;">Chat</span></a>`;
                });
            } else { resultsBox.innerHTML = '<div style="padding:32px;text-align:center;color:#6ee7b7;">No contacts found.</div>'; }
        }).catch(err => console.error(err));
    }

    function scrollToBottom() { const chatBox = document.getElementById('chat-messages'); if (chatBox) chatBox.scrollTop = chatBox.scrollHeight; }
</script>
@endsection
