@extends('super-admin.layouts.app')

@section('content')
<style>
    .chat-page-wrap { display:flex; height:calc(100vh - 80px); border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(13,110,253,0.1); border:1px solid #dee2e6; }
    .chat-sidebar { width:300px; min-width:260px; background:#fff; border-right:1px solid #dee2e6; display:flex; flex-direction:column; }
    .chat-sidebar-header { padding:14px 16px; background:#212529; display:flex; align-items:center; justify-content:space-between; }
    .tab-bar { display:flex; border-bottom:1px solid #dee2e6; }
    .tab-btn { flex:1; padding:8px; background:none; border:none; font-size:0.82rem; font-weight:600; color:#6c757d; cursor:pointer; border-bottom:2px solid transparent; }
    .tab-btn.active { color:#0d6efd; border-bottom-color:#0d6efd; }
    .tab-pane { display:none; }
    .tab-pane.active { display:block; }
    .convo-list { overflow-y:auto; }
    .convo-item { display:block; padding:12px 16px; border-bottom:1px solid #f1f3f5; text-decoration:none; color:inherit; cursor:pointer; transition:background .15s; }
    .convo-item:hover { background:#f8f9fa; }
    .convo-item.active { background:#e7f1ff; border-left:4px solid #0d6efd; }
    .convo-name { font-weight:700; font-size:0.88rem; color:#212529; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:170px; }
    .convo-time { font-size:0.7rem; color:#6c757d; white-space:nowrap; }
    .convo-preview { font-size:0.78rem; color:#6c757d; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .unread-badge { background:#dc3545; color:#fff; font-size:0.65rem; font-weight:800; min-width:18px; height:18px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; padding:0 4px; }
    .chat-area { flex:1; display:flex; flex-direction:column; background:#f8f9fa; min-width:0; }
    .chat-area-header { padding:14px 20px; background:#fff; border-bottom:1px solid #dee2e6; }
    .msg-list { flex:1; overflow-y:auto; padding:20px 24px; display:flex; flex-direction:column; gap:6px; }
    .chat-input-area { padding:12px 16px; background:#fff; border-top:1px solid #dee2e6; }
    .msg-bubble { max-width:68%; padding:9px 14px; border-radius:14px; word-wrap:break-word; font-size:0.88rem; }
    .msg-outgoing { background:#0d6efd; color:#fff; margin-left:auto; border-bottom-right-radius:3px; }
    .msg-incoming { background:#e4e6eb; color:#212529; margin-right:auto; border-bottom-left-radius:3px; }
    .msg-time { font-size:0.68rem; opacity:.75; margin-top:4px; display:flex; justify-content:flex-end; align-items:center; gap:3px; }
    .msg-tick { font-size:.8rem; }
    .msg-tick.read { color:#53bdeb; }
    .attach-preview { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
    .attach-preview-item { position:relative; }
    .attach-preview-item img { width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #dee2e6; }
    .attach-preview-item .remove-file { position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:18px; height:18px; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .file-doc-chip { background:#e7f1ff; border:1px solid #bee2fd; border-radius:8px; padding:4px 10px; font-size:0.75rem; color:#0d6efd; display:flex; align-items:center; gap:5px; }
    .input-row { display:flex; gap:8px; align-items:flex-end; }
    .attach-btn-sq { width:38px; height:38px; border-radius:8px; background:#e7f1ff; border:none; color:#0d6efd; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .attach-btn-sq:hover { background:#bee2fd; }
    .msg-input { flex:1; border:1.5px solid #dee2e6; border-radius:22px; padding:9px 16px; font-size:0.9rem; outline:none; background:#fff; resize:none; max-height:120px; min-height:38px; }
    .send-btn { background:#0d6efd; color:#fff; border:none; border-radius:22px; padding:9px 22px; font-weight:700; cursor:pointer; white-space:nowrap; }
    .send-btn:disabled { opacity:.5; cursor:default; }
    .msg-attachment { margin-top:6px; }
    .msg-attachment img { max-width:220px; border-radius:10px; display:block; cursor:pointer; }
    .msg-attachment audio { display:block; margin-top:4px; width:200px; }
    .msg-attachment .doc-link { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.25); border-radius:8px; padding:6px 12px; font-size:0.8rem; color:inherit; text-decoration:none; }
    .sidebar-scroller { flex:1; overflow-y:auto; }
</style>

<div class="container-fluid py-2">
<div class="chat-page-wrap">
    {{-- Sidebar --}}
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <div>
                <div style="color:#fff;font-weight:800;font-size:1rem;">💬 Super Admin Chat</div>
                <small style="color:#adb5bd;font-size:0.75rem;">{{ $user->name ?? $user->full_name ?? 'SuperAdmin' }}</small>
            </div>
            <button type="button" class="attach-btn-sq" style="background:rgba(255,255,255,.15);color:#fff;" data-bs-toggle="modal" data-bs-target="#newChatModal" title="New Message">+</button>
        </div>
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('chats',this)">Chats</button>
            <button class="tab-btn" onclick="switchTab('admins',this)">Admin List</button>
        </div>
        <div class="sidebar-scroller">
            <div class="tab-pane active" id="tab-chats">
                <div id="superadmin-convo-list">
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
                        <div class="p-4 text-center text-muted">
                            <p class="mb-2">No personal chats found.</p>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">Start a Chat</button>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="tab-pane" id="tab-admins">
                @if(isset($admins) && count($admins) > 0)
                    @foreach($admins as $adminItem)
                        @php $adminObj = is_array($adminItem) ? (object)$adminItem : $adminItem; @endphp
                        <a href="javascript:void(0)" onclick="startChatAjax('{{ route('super-admin.chat.start', ['receiverId'=>$adminObj->id??$adminObj->user_id,'type'=>'admin']) }}','{{ addslashes($adminObj->name??$adminObj->full_name??'Admin') }}')"
                           class="convo-item">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="convo-name">{{ $adminObj->name ?? $adminObj->full_name ?? 'Admin' }}</div>
                                    <small class="text-muted" style="font-size:0.75rem;">{{ $adminObj->email ?? $adminObj->user_code ?? '' }}</small>
                                </div>
                                <span class="badge bg-primary">Chat</span>
                            </div>
                        </a>
                    @endforeach
                @elseif(isset($suggestedContacts) && count($suggestedContacts) > 0)
                    @foreach($suggestedContacts as $contact)
                        @php $contactObj = is_array($contact) ? (object)$contact : $contact; @endphp
                        <a href="javascript:void(0)" onclick="startChatAjax('{{ route('super-admin.chat.start', ['receiverId'=>$contactObj->id??$contactObj->user_id,'type'=>$contactObj->type??'admin']) }}','{{ addslashes($contactObj->name??'User') }}')"
                           class="convo-item">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="convo-name">{{ $contactObj->name ?? 'User' }}</div>
                                    <small class="text-muted" style="font-size:0.75rem;">{{ $contactObj->code ?? '' }}</small>
                                </div>
                                <span class="badge bg-primary">Chat</span>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="p-4 text-center text-muted">No admins found.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Chat Area --}}
    <div class="chat-area">
        <div class="chat-area-header">
            <h6 class="mb-0 fw-bold" id="chat-title">Select a conversation</h6>
            <small class="text-muted" id="chat-subtitle">No active chat</small>
        </div>
        <div class="msg-list" id="chat-messages">
            <div class="d-flex h-100 align-items-center justify-content-center text-muted flex-column">
                <p class="mb-0">Select a conversation or start a new chat.</p>
            </div>
        </div>
        <div class="chat-input-area">
            <div class="attach-preview" id="attach-preview"></div>
            <form id="chat-form" onsubmit="window.handleSendMessage(event)">
                <input type="hidden" id="active_conversation_id" value="">
                <input type="file" id="file-input" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.mp3,.webm,.ogg" style="display:none;" onchange="handleFileSelect(this)">
                <div class="input-row">
                    <button type="button" class="attach-btn-sq" onclick="document.getElementById('file-input').click()" title="Attach file">📎</button>
                    <textarea class="msg-input" id="message-input" placeholder="Type a message..." autocomplete="off" rows="1" disabled
                        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();window.handleSendMessage(event);}"></textarea>
                    <button type="submit" class="send-btn" id="send-btn" disabled>Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

{{-- Modal --}}
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Start Chat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <input type="text" id="chat-search-input" class="form-control" placeholder="Search users by name or code..." oninput="searchChatUsers(this.value)">
                </div>
                <div id="chat-search-results" style="max-height:350px;overflow-y:auto;">
                    <div class="p-4 text-center text-muted">Type to search for admins, buyers, or craftsmen...</div>
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
    const sendRouteUrl     = "{{ route('super-admin.chat.send') }}";
    const showRouteBaseUrl = "{{ url('super-admin/chat') }}";
    const searchRouteUrl   = "{{ route('super-admin.chat.search') }}";
    const markDeliveredUrl = "{{ route('chat.message.delivered') }}";
    const markReadUrl      = "{{ route('chat.message.read') }}";
    const csrfToken        = "{{ csrf_token() }}";

    let activeConversationId = null;
    let unreadMessageIds = [];
    let selectedFiles = [];

    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-'+tab).classList.add('active');
        btn.classList.add('active');
    }

    window.Pusher = Pusher;
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost: '{{ config("broadcasting.connections.reverb.options.host","localhost") }}',
            wsPort: {{ (int)config("broadcasting.connections.reverb.options.port",8080) }},
            wssPort: {{ (int)config("broadcasting.connections.reverb.options.port",8080) }},
            forceTLS: false, enabledTransports: ['ws','wss'],
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrfToken } }
        });
    } catch(e) { console.error("Echo init error:", e); }

    window.startChatAjax = function(url, titleName) {
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'application/json' } })
            .then(r => r.json())
            .then(data => {
                const modalEl = document.getElementById('newChatModal');
                if (modalEl) { const mi=bootstrap.Modal.getInstance(modalEl)||new bootstrap.Modal(modalEl); mi.hide(); }
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');

                if (data.conversation_id) {
                    if (!document.querySelector(`.convo-item[data-id="${data.conversation_id}"]`)) {
                        const list = document.getElementById('superadmin-convo-list');
                        const link = document.createElement('a');
                        link.href='javascript:void(0)'; link.className='convo-item';
                        link.dataset.id=data.conversation_id; link.dataset.title=titleName;
                        link.onclick=()=>window.loadConversation(data.conversation_id,titleName);
                        link.innerHTML=`<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                            <span class="convo-name">${titleName}</span>
                            <div style="display:flex;align-items:center;gap:5px;">
                                <span class="convo-time">Just now</span>
                                <span class="unread-badge" id="badge-${data.conversation_id}" style="display:none;">0</span>
                            </div></div>
                            <span class="convo-preview" id="preview-${data.conversation_id}">Click to open conversation</span>`;
                        list.insertBefore(link, list.firstChild);
                    }
                    // Switch to Chats tab
                    switchTab('chats', document.querySelector('.tab-btn'));
                    window.loadConversation(data.conversation_id, titleName);
                }
            }).catch(err => console.error(err));
    };

    window.loadConversation = function(conversationId, titleName) {
        activeConversationId = conversationId;
        document.getElementById('active_conversation_id').value = conversationId;
        const msgInput = document.getElementById('message-input');
        const sendBtn  = document.getElementById('send-btn');
        msgInput.disabled = false; msgInput.focus(); sendBtn.disabled = false;
        document.getElementById('chat-title').innerText    = titleName;
        document.getElementById('chat-subtitle').innerText = 'Connected in real-time';
        document.querySelectorAll('.convo-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.querySelector(`.convo-item[data-id="${conversationId}"]`);
        if (activeItem) activeItem.classList.add('active');
        const badge = document.getElementById(`badge-${conversationId}`);
        if (badge) { badge.innerText='0'; badge.style.display='none'; }

        const chatBox = document.getElementById('chat-messages');
        chatBox.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted">Loading messages...</div>';
        fetch(`${showRouteBaseUrl}/${conversationId}`, { headers:{'Accept':'application/json'} })
            .then(r => r.json())
            .then(data => {
                chatBox.innerHTML = '';
                if (data.messages && data.messages.length > 0) data.messages.forEach(msg => appendMessage(msg));
                else chatBox.innerHTML = '<div class="text-center text-muted pt-5">No messages yet. Say hello! 👋</div>';
            }).catch(() => { chatBox.innerHTML = '<div class="text-danger text-center pt-5">Error loading messages.</div>'; });
    };

    window.attachConversationListener = function(cId) {
        if (!window.Echo) return;
        window.Echo.private(`conversation.${cId}`)
            .listen('.message.sent', (e) => {
                if (activeConversationId == cId) {
                    appendMessage(e); scrollToBottom();
                    markAsDelivered([e.id]);
                    if (document.visibilityState==='visible') markAsRead([e.id]);
                    else unreadMessageIds.push(e.id);
                } else {
                    const badge=document.getElementById(`badge-${cId}`);
                    if(badge){let c=parseInt(badge.innerText)||0;c++;badge.innerText=c;badge.style.display='inline-flex';}
                }
                moveConversationToTop(cId, e.body||'📎 Attachment', 'Just now');
            })
            .listen('.message.delivered', (e) => { 
                if (activeConversationId == cId) { const t=document.getElementById(`tick-${e.messageId}`); if(t) t.innerHTML='✓✓'; }
            })
            .listen('.message.read', (e) => { 
                if (activeConversationId == cId) { const t=document.getElementById(`tick-${e.messageId}`); if(t){t.innerHTML='✓✓';t.classList.add('read');} }
            })
            .listen('.message.deleted', (e) => { 
                if (activeConversationId == cId) { const el=document.getElementById(`msg-${e.messageId}`); if(el) el.remove(); }
            });
    };

    @foreach($conversations as $convo)
        attachConversationListener({{ (int)$convo->id }});
    @endforeach

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState==='visible'&&activeConversationId&&unreadMessageIds.length>0) { markAsRead(unreadMessageIds); unreadMessageIds=[]; }
    });

    function markAsDelivered(ids) { fetch(markDeliveredUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},body:JSON.stringify({message_ids:ids})}); }
    function markAsRead(ids) { fetch(markReadUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},body:JSON.stringify({message_ids:ids})}); }

    // Listen to Global User Channel for NEW conversations
    (function() {
        const userChannelType = "{{ strtolower(class_basename(get_class($user))) }}";
        if (window.Echo) {
            window.Echo.private(`user.${userChannelType}.${authUserId}`)
                .listen('.message.sent', (e) => {
                    const list = document.getElementById('superadmin-convo-list');
                    if (list && !list.querySelector(`.convo-item[data-id="${e.conversation_id}"]`)) {
                        window.location.reload();
                    }
                });
        }
    })();

    function handleFileSelect(input) {
        Array.from(input.files).forEach(file => {
            const mb=file.size/1024/1024, limit=file.type.startsWith('image/')?10:25;
            if(mb>limit){alert(`${file.name} exceeds ${limit}MB limit.`);return;}
            selectedFiles.push(file);
        });
        input.value=''; renderAttachPreview();
    }

    function renderAttachPreview() {
        const box=document.getElementById('attach-preview'); box.innerHTML='';
        selectedFiles.forEach((file,idx)=>{
            const item=document.createElement('div'); item.className='attach-preview-item';
            if(file.type.startsWith('image/')){const img=document.createElement('img');img.src=URL.createObjectURL(file);item.appendChild(img);}
            else{const chip=document.createElement('div');chip.className='file-doc-chip';chip.innerHTML=`📄 ${file.name.substring(0,20)}${file.name.length>20?'…':''}`;item.appendChild(chip);}
            const rm=document.createElement('button');rm.className='remove-file';rm.innerHTML='×';
            rm.onclick=()=>{selectedFiles.splice(idx,1);renderAttachPreview();};item.appendChild(rm);
            box.appendChild(item);
        });
    }

    window.handleSendMessage = function(e) {
        if(e&&e.preventDefault) e.preventDefault();
        const input=document.getElementById('message-input'), text=input.value.trim();
        const convoId=document.getElementById('active_conversation_id').value;
        if(!convoId||(!text&&selectedFiles.length===0)) return;
        input.value='';
        const fd=new FormData();
        fd.append('conversation_id',convoId); fd.append('body',text);
        selectedFiles.forEach((f,i)=>fd.append(`attachments[${i}]`,f));
        selectedFiles=[]; renderAttachPreview();
        const socketId=(window.Echo&&window.Echo.socketId)?window.Echo.socketId():'';
        fetch(sendRouteUrl,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Socket-ID':socketId},body:fd})
            .then(r=>r.json())
            .then(msg=>{
                appendMessage(msg);
                scrollToBottom(); moveConversationToTop(convoId,msg.body||'📎 Attachment','Just now');
            }).catch(err=>console.error(err));
    };

    function moveConversationToTop(convoId,lastMsg) {
        const list=document.getElementById('superadmin-convo-list'); if(!list) return;
        const item=list.querySelector(`.convo-item[data-id="${convoId}"]`);
        if(item){const p=document.getElementById(`preview-${convoId}`);if(p&&lastMsg)p.innerText=lastMsg;list.insertBefore(item,list.firstChild);}
    }

    function appendMessage(msg) {
        const chatBox=document.getElementById('chat-messages'); if(!chatBox) return;
        const ph=chatBox.querySelector('.text-muted,.text-center'); if(ph) ph.remove();
        if(msg.id&&document.getElementById(`msg-${msg.id}`)) return;
        const isMe=(parseInt(msg.sender_id)===parseInt(authUserId))&&(!msg.sender_type||msg.sender_type===authUserType);
        let tickHtml='';
        if(isMe){
            const isRead=(msg.statuses||[]).some(s=>s.read_at);
            const isDlvr=(msg.statuses||[]).some(s=>s.delivered_at);
            if(isRead) tickHtml=`<span class="msg-tick read" id="tick-${msg.id}">✓✓</span>`;
            else if(isDlvr) tickHtml=`<span class="msg-tick" id="tick-${msg.id}">✓✓</span>`;
            else tickHtml=`<span class="msg-tick" id="tick-${msg.id}">✓</span>`;
        } else { if(!msg.is_read) unreadMessageIds.push(msg.id); }

        let attHtml='';
        if(msg.attachments&&msg.attachments.length>0){
            msg.attachments.forEach(at=>{
                const url=at.url||`/storage/${at.file_path}`;
                if(at.file_type==='image') attHtml+=`<div class="msg-attachment"><img src="${url}" onclick="window.open('${url}');"></div>`;
                else if(at.file_type==='voice') attHtml+=`<div class="msg-attachment"><audio controls src="${at.base64_data||url}"></audio></div>`;
                else attHtml+=`<div class="msg-attachment"><a class="doc-link" href="${url}" target="_blank">📄 ${at.file_name}</a></div>`;
            });
        }

        const time=msg.created_at?(typeof msg.created_at==='string'&&msg.created_at.includes('T')?new Date(msg.created_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}):msg.created_at):'';
        const bubble=document.createElement('div');
        if(msg.id) bubble.id=`msg-${msg.id}`;
        bubble.className=`msg-bubble ${isMe?'msg-outgoing':'msg-incoming'}`;
        bubble.innerHTML=`<div style="font-size:0.75rem;font-weight:600;margin-bottom:2px;">${isMe?'You':(msg.sender_name||'User')}</div>
            ${msg.body?`<div>${msg.body}</div>`:''}${attHtml}
            <div class="msg-time"><span>${time}</span>${tickHtml}</div>`;
        chatBox.appendChild(bubble);
    }

    function searchChatUsers(query) {
        const resultsBox=document.getElementById('chat-search-results');
        fetch(`${searchRouteUrl}?q=${encodeURIComponent(query)}`).then(r=>r.json()).then(data=>{
            resultsBox.innerHTML='';
            if(data.length>0){
                data.forEach(contact=>{
                    const url=`{{ url('super-admin/chat/start') }}/${contact.id}/${contact.type}`;
                    resultsBox.innerHTML+=`<a href="javascript:void(0)" onclick="startChatAjax('${url}','${contact.name.replace(/'/g,"\\'")}') "
                        class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between">
                        <div><h6 class="mb-0 fw-bold">${contact.name}</h6><small class="text-muted">${contact.code??''}</small></div>
                        <span class="btn btn-sm btn-dark">Start Chat</span></a>`;
                });
            } else { resultsBox.innerHTML='<div class="p-4 text-center text-muted">No users found.</div>'; }
        }).catch(err=>console.error(err));
    }

    function scrollToBottom() { const chatBox=document.getElementById('chat-messages'); if(chatBox) chatBox.scrollTop=chatBox.scrollHeight; }
</script>
@endsection