{{-- Reusable Unified Real-Time Web Chat Component --}}
@php
    $panelKey = $panel ?? 'super-admin';
    $panelTheme = match($panelKey) {
        'admin' => [
            'name'       => 'Admin',
            'primary'    => '#97144d',
            'dark'       => '#831843',
            'accent'     => '#db2777',
            'light'      => '#fdf2f8',
            'border'     => '#fbcfe8',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#97144d',
        ],
        'buyer' => [
            'name'       => 'Buyer',
            'primary'    => '#4f46e5',
            'dark'       => '#3730a3',
            'accent'     => '#6366f1',
            'light'      => '#eef2ff',
            'border'     => '#c7d2fe',
            'badge_bg'   => '#e0e7ff',
            'badge_text' => '#4338ca',
        ],
        'craftsman' => [
            'name'       => 'Craftsman',
            'primary'    => '#065f46',
            'dark'       => '#064e3b',
            'accent'     => '#047857',
            'light'      => '#ecfdf5',
            'border'     => '#a7f3d0',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
        ],
        default => [
            'name'       => 'Super Admin',
            'primary'    => '#7a1c28',
            'dark'       => '#5a1019',
            'accent'     => '#962232',
            'light'      => '#fdf2f4',
            'border'     => '#f1d4d8',
            'badge_bg'   => '#fae8eb',
            'badge_text' => '#7a1c28',
        ],
    };
@endphp

<style>
/* Scoped Chat Application Styles - Universal Across All Panels */
#chatApp {
    --chat-primary: {{ $panelTheme['primary'] }};
    --chat-dark: {{ $panelTheme['dark'] }};
    --chat-accent: {{ $panelTheme['accent'] }};
    --chat-light: {{ $panelTheme['light'] }};
    --chat-border: {{ $panelTheme['border'] }};
    --chat-badge-bg: {{ $panelTheme['badge_bg'] }};
    --chat-badge-text: {{ $panelTheme['badge_text'] }};

    display: flex;
    flex-direction: column;
    height: calc(100vh - 160px);
    min-height: 560px;
    max-height: 920px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    position: relative;
    font-family: inherit;
}

html.dark #chatApp,
.dark #chatApp {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
}

.chat-d-none {
    display: none !important;
}

.chat-body-container {
    display: flex;
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

/* Sidebar Styles */
#sidebarPane.chat-sidebar {
    width: 360px;
    min-width: 300px;
    max-width: 400px;
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    height: 100%;
    z-index: 10;
    flex-shrink: 0;
}

html.dark #sidebarPane.chat-sidebar,
.dark #sidebarPane.chat-sidebar {
    background: #1e293b;
    border-right-color: #334155;
}

.chat-sidebar-header {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

html.dark .chat-sidebar-header,
.dark .chat-sidebar-header {
    background: #0f172a;
    border-bottom-color: #334155;
}

.chat-user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: var(--chat-primary);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
    flex-shrink: 0;
    overflow: hidden;
}

.chat-user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-btn-new-chat {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--chat-primary);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 15px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    transition: transform 0.15s ease, filter 0.15s ease;
}

.chat-btn-new-chat:hover {
    filter: brightness(1.12);
    transform: scale(1.05);
}

.chat-mode-tabs {
    display: flex;
    padding: 3px;
    background: #e2e8f0;
    border-radius: 10px;
    margin-bottom: 12px;
    font-size: 12px;
    font-weight: 700;
    gap: 4px;
}

html.dark .chat-mode-tabs,
.dark .chat-mode-tabs {
    background: #334155;
}

.chat-mode-tab {
    flex: 1;
    padding: 6px 10px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

html.dark .chat-mode-tab,
.dark .chat-mode-tab {
    color: #94a3b8;
}

.chat-mode-tab.active {
    background: #ffffff;
    color: #1e293b;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

html.dark .chat-mode-tab.active,
.dark .chat-mode-tab.active {
    background: #0f172a;
    color: #f1f5f9;
}

.chat-search-wrap {
    position: relative;
    width: 100%;
}

.chat-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 12px;
    pointer-events: none;
}

.chat-search-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    font-size: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    outline: none;
    color: #1e293b;
    transition: border-color 0.15s ease;
}

.chat-search-input:focus {
    border-color: var(--chat-primary);
}

html.dark .chat-search-input,
.dark .chat-search-input {
    background: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
}

html.dark .chat-search-input:focus,
.dark .chat-search-input:focus {
    border-color: var(--chat-accent);
}

#conversationsList {
    flex: 1;
    overflow-y: auto;
    background: #ffffff;
}

html.dark #conversationsList,
.dark #conversationsList {
    background: #1e293b;
}

.chat-conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.15s ease;
    text-decoration: none;
}

html.dark .chat-conv-item,
.dark .chat-conv-item {
    border-bottom-color: #334155;
}

.chat-conv-item:hover {
    background: #f8fafc;
}

html.dark .chat-conv-item:hover,
.dark .chat-conv-item:hover {
    background: #334155;
}

.chat-conv-item.active {
    background: var(--chat-light) !important;
    border-left: 4px solid var(--chat-primary) !important;
}

html.dark .chat-conv-item.active,
.dark .chat-conv-item.active {
    background: rgba(255, 255, 255, 0.08) !important;
    border-left: 4px solid var(--chat-primary) !important;
}

.chat-conv-name {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

html.dark .chat-conv-name,
.dark .chat-conv-name {
    color: #f8fafc;
}

.chat-conv-time {
    font-size: 11px;
    color: #94a3b8;
    flex-shrink: 0;
}

.chat-conv-preview {
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
    line-height: 1.3;
}

html.dark .chat-conv-preview,
.dark .chat-conv-preview {
    color: #94a3b8;
}

.chat-conv-badge {
    background: var(--chat-primary);
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 9999px;
    margin-left: 8px;
    flex-shrink: 0;
}

/* Main Pane Styles */
#mainChatPane.chat-main-pane {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #f8fafc;
    position: relative;
    min-width: 0;
}

html.dark #mainChatPane.chat-main-pane,
.dark #mainChatPane.chat-main-pane {
    background: #0f172a;
}

.chat-empty-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px;
    text-align: center;
}

.chat-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #64748b;
    margin-bottom: 16px;
}

html.dark .chat-empty-icon,
.dark .chat-empty-icon {
    background: #1e293b;
    color: #94a3b8;
}

.chat-empty-title {
    font-size: 16px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 6px;
}

html.dark .chat-empty-title,
.dark .chat-empty-title {
    color: #f1f5f9;
}

.chat-empty-desc {
    font-size: 12px;
    color: #64748b;
    max-width: 380px;
    margin-bottom: 18px;
    line-height: 1.5;
}

html.dark .chat-empty-desc,
.dark .chat-empty-desc {
    color: #94a3b8;
}

.chat-empty-btn {
    padding: 9px 20px;
    border-radius: 10px;
    background: var(--chat-primary);
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: filter 0.15s ease, transform 0.1s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.chat-empty-btn:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.chat-top-header {
    padding: 12px 20px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    z-index: 5;
}

html.dark .chat-top-header,
.dark .chat-top-header {
    background: #1e293b;
    border-bottom-color: #334155;
}

.chat-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #059669;
    background: #ecfdf5;
    padding: 4px 10px;
    border-radius: 9999px;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

html.dark .chat-status-pill,
.dark .chat-status-pill {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.3);
}

.chat-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    animation: chat-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes chat-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.35; }
}

#messagesContainer {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #f8fafc;
}

html.dark #messagesContainer,
.dark #messagesContainer {
    background: #0f172a;
}

.chat-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    width: 100%;
}

.chat-msg-row.mine {
    justify-content: flex-end;
}

.chat-msg-row.theirs {
    justify-content: flex-start;
}

.chat-msg-bubble {
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 16px;
    position: relative;
    word-break: break-word;
}

.chat-msg-bubble.mine {
    background: var(--chat-primary);
    color: #ffffff;
    border-bottom-right-radius: 3px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.chat-msg-bubble.theirs {
    background: #ffffff;
    color: #1e293b;
    border-bottom-left-radius: 3px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

html.dark .chat-msg-bubble.theirs,
.dark .chat-msg-bubble.theirs {
    background: #1e293b;
    color: #f1f5f9;
    border-color: #334155;
}

.chat-msg-sender {
    font-size: 11px;
    font-weight: 700;
    color: var(--chat-accent);
    margin-bottom: 3px;
}

.chat-msg-text {
    font-size: 13px;
    line-height: 1.5;
    white-space: pre-line;
}

.chat-msg-meta {
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
    opacity: 0.8;
}

.chat-msg-bubble.mine .chat-msg-meta {
    color: rgba(255, 255, 255, 0.85);
}

.chat-msg-bubble.theirs .chat-msg-meta {
    color: #94a3b8;
}

.btn-delete-msg {
    opacity: 0;
    transition: opacity 0.15s ease, color 0.15s ease;
    padding: 4px 6px;
    color: #94a3b8;
    background: transparent;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.chat-msg-row:hover .btn-delete-msg {
    opacity: 1;
}

.btn-delete-msg:hover {
    color: #ef4444;
}

.chat-att-img-wrap {
    margin-top: 6px;
    margin-bottom: 4px;
    cursor: pointer;
    border-radius: 10px;
    overflow: hidden;
    max-width: 260px;
}

.chat-att-img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 10px;
    transition: opacity 0.15s ease;
}

.chat-att-img:hover {
    opacity: 0.92;
}

.chat-att-file {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none !important;
    margin-top: 6px;
    transition: background 0.15s ease;
}

.chat-att-file.mine {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
}

.chat-att-file.mine:hover {
    background: rgba(255, 255, 255, 0.25);
}

.chat-att-file.theirs {
    background: #f1f5f9;
    color: #334155;
}

.chat-att-file.theirs:hover {
    background: #e2e8f0;
}

html.dark .chat-att-file.theirs,
.dark .chat-att-file.theirs {
    background: #334155;
    color: #f1f5f9;
}

#attachmentPreviewBanner {
    padding: 8px 16px;
    background: #e2e8f0;
    border-top: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
}

html.dark #attachmentPreviewBanner,
.dark #attachmentPreviewBanner {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}

.chat-input-bar {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

html.dark .chat-input-bar,
.dark .chat-input-bar {
    background: #1e293b;
    border-top-color: #334155;
}

.chat-btn-attach {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.15s ease, color 0.15s ease;
    flex-shrink: 0;
}

.chat-btn-attach:hover {
    background: #e2e8f0;
    color: #1e293b;
}

html.dark .chat-btn-attach,
.dark .chat-btn-attach {
    background: #334155;
    color: #cbd5e1;
}

html.dark .chat-btn-attach:hover,
.dark .chat-btn-attach:hover {
    background: #475569;
    color: #ffffff;
}

.chat-input-textarea {
    flex: 1;
    min-height: 40px;
    max-height: 120px;
    padding: 9px 14px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    color: #1e293b;
    resize: none;
    outline: none;
    font-family: inherit;
    line-height: 1.4;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.chat-input-textarea:focus {
    border-color: var(--chat-primary);
    background: #ffffff;
}

html.dark .chat-input-textarea,
.dark .chat-input-textarea {
    background: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
}

html.dark .chat-input-textarea:focus,
.dark .chat-input-textarea:focus {
    border-color: var(--chat-accent);
    background: #0f172a;
}

.chat-btn-send {
    height: 40px;
    padding: 0 18px;
    border-radius: 12px;
    border: none;
    background: var(--chat-primary);
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.1s ease, filter 0.15s ease;
    flex-shrink: 0;
}

.chat-btn-send:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.chat-btn-send:active {
    transform: translateY(0);
}

.chat-btn-send:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
}

.chat-role-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 9999px;
    background: var(--chat-badge-bg);
    color: var(--chat-badge-text);
}

.chat-contact-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    margin-bottom: 8px;
}

.chat-contact-item:hover {
    background: #f8fafc;
    border-color: var(--chat-primary);
    transform: translateY(-1px);
}

html.dark .chat-contact-item,
.dark .chat-contact-item {
    background: #1e293b;
    border-color: #334155;
    color: #f1f5f9;
}

html.dark .chat-contact-item:hover,
.dark .chat-contact-item:hover {
    background: #334155;
    border-color: var(--chat-accent);
}

.spin-animation {
    display: inline-block;
    animation: chat-spin 1s linear infinite;
}

@keyframes chat-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 767px) {
    #chatApp {
        height: calc(100vh - 120px);
        border-radius: 12px;
    }
    #sidebarPane.chat-sidebar {
        width: 100% !important;
        max-width: 100% !important;
        border-right: none;
    }
    #mainChatPane.chat-main-pane {
        width: 100% !important;
    }
    .chat-hide-on-mobile {
        display: none !important;
    }
}
</style>

<div class="chat-app-wrapper" id="chatApp">
    <div class="chat-body-container">

        {{-- LEFT SIDEBAR: Conversations & Contacts --}}
        <div class="chat-sidebar" id="sidebarPane">
            {{-- Header --}}
            <div class="chat-sidebar-header">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="chat-user-avatar">
                            @if(!empty($authUser['avatar']))
                                <img src="{{ $authUser['avatar'] }}" alt="Me">
                            @else
                                {{ strtoupper(substr($authUser['name'] ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <h3 style="font-size: 14px; font-weight: 800; color: #1e293b; margin: 0; line-height: 1.2;" class="dark:text-white">
                                {{ $authUser['name'] ?? 'User' }}
                            </h3>
                            <span class="chat-role-badge" style="margin-top: 3px;">
                                {{ $authUser['badge'] ?? 'User' }}
                            </span>
                        </div>
                    </div>

                    <button type="button" id="btnNewChat" class="chat-btn-new-chat" title="Start New Chat">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </div>

                {{-- SuperAdmin Mode Switcher (My Chats vs All System Chats) --}}
                @if(!empty($isSuperAdmin))
                <div class="chat-mode-tabs">
                    <button type="button" id="tabMyChats" class="chat-mode-tab active">
                        <i class="bi bi-chat-text"></i> My Chats
                    </button>
                    <button type="button" id="tabAllChats" class="chat-mode-tab">
                        <i class="bi bi-shield-lock"></i> Monitor All Chats
                    </button>
                </div>
                @endif

                {{-- Search Bar --}}
                <div class="chat-search-wrap">
                    <i class="bi bi-search chat-search-icon"></i>
                    <input type="text" id="chatSearchInput" placeholder="Search conversations..." class="chat-search-input">
                </div>
            </div>

            {{-- Conversations List Container --}}
            <div id="conversationsList">
                <div style="padding: 32px; text-align: center; color: #94a3b8; font-size: 12px;">
                    <i class="bi bi-arrow-repeat spin-animation" style="font-size: 20px; display: block; margin-bottom: 8px;"></i>
                    Loading chats...
                </div>
            </div>
        </div>

        {{-- RIGHT MAIN PANE: Messages & Active Chat --}}
        <div class="chat-main-pane chat-hide-on-mobile" id="mainChatPane">

            {{-- Empty State (No Chat Selected) --}}
            <div id="noChatSelected" class="chat-empty-container">
                <div class="chat-empty-icon">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <h3 class="chat-empty-title">Instant Real-Time Chat</h3>
                <p class="chat-empty-desc">
                    Select an existing conversation from the list or start a new conversation. Messages synchronize instantly in real-time with the Mobile App.
                </p>
                <button type="button" class="btnStartNewChatHero chat-empty-btn">
                    <i class="bi bi-plus-lg"></i> Start Conversation
                </button>
            </div>

            {{-- Active Chat Screen (Initially Hidden) --}}
            <div id="activeChatScreen" class="chat-d-none" style="flex: 1; display: flex; flex-direction: column; height: 100%; min-height: 0;">
                {{-- Active Chat Top Header --}}
                <div class="chat-top-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <button type="button" id="btnBackToSidebar" class="d-md-none" style="padding: 6px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-size: 16px; cursor: pointer;">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="chat-user-avatar" id="activePartyAvatar">
                            U
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h4 style="font-size: 14px; font-weight: 800; color: #1e293b; margin: 0; line-height: 1.2;" id="activePartyName" class="dark:text-white">Contact Name</h4>
                                <span class="chat-role-badge" id="activePartyBadge">Role</span>
                            </div>
                            <span style="font-size: 11px; color: #94a3b8;" id="activePartyCode">#ID</span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="chat-status-pill">
                            <span class="chat-status-dot"></span>
                            Live Pusher Sync
                        </span>
                    </div>
                </div>

                {{-- Messages Thread Container --}}
                <div id="messagesContainer">
                    {{-- Messages dynamically loaded here --}}
                </div>

                {{-- Attachment Preview Banner --}}
                <div id="attachmentPreviewBanner" class="chat-d-none">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-paperclip" style="font-size: 16px; color: #64748b;"></i>
                        <span id="attachmentFileName" style="font-weight: 700; color: #334155; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">filename.pdf</span>
                    </div>
                    <button type="button" id="btnRemoveAttachment" style="border: none; background: transparent; color: #ef4444; font-size: 16px; cursor: pointer; padding: 2px 6px;" title="Remove attachment">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                {{-- Bottom Message Input Bar --}}
                <div class="chat-input-bar">
                    <form id="sendMessageForm" action="javascript:void(0);" method="POST" onsubmit="event.preventDefault(); return false;" style="display: flex; align-items: flex-end; gap: 8px; width: 100%; margin: 0;">
                        @csrf
                        <input type="file" id="fileAttachmentInput" style="display: none;" accept="image/*,.pdf,.doc,.docx,.txt,.zip">
                        
                        <button type="button" id="btnAttachFile" class="chat-btn-attach" title="Attach image or file">
                            <i class="bi bi-paperclip"></i>
                        </button>

                        <div style="flex: 1; position: relative;">
                            <textarea id="messageTextInput" rows="1" placeholder="Type a message... (Enter to send, Shift+Enter for new line)" class="chat-input-textarea"></textarea>
                        </div>

                        <button type="button" id="btnSendMessage" class="chat-btn-send">
                            <span>Send</span>
                            <i class="bi bi-send-fill" style="font-size: 12px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Start New Chat (Contact List) --}}
<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header" style="background: var(--chat-primary); color: #ffffff; padding: 14px 20px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-chat-plus" style="font-size: 18px;"></i>
                    <h5 class="modal-title" id="newChatModalLabel" style="font-size: 14px; font-weight: 800; margin: 0;">Start New Conversation</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 16px; background: #f8fafc;">
                {{-- Search inside modal --}}
                <div class="chat-search-wrap" style="margin-bottom: 12px;">
                    <i class="bi bi-search chat-search-icon"></i>
                    <input type="text" id="contactSearchInput" placeholder="Search contacts by name or code..." class="chat-search-input">
                </div>

                {{-- Contacts List --}}
                <div style="max-height: 320px; overflow-y: auto;" id="contactsListContainer">
                    <div style="text-align: center; padding: 32px; color: #94a3b8; font-size: 12px;">
                        <i class="bi bi-arrow-repeat spin-animation" style="font-size: 20px; display: block; margin-bottom: 8px;"></i>
                        Loading eligible contacts...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Confirm Delete (SuperAdmin Only) --}}
@if(!empty($isSuperAdmin))
<div class="modal fade" id="deleteMsgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-body" style="padding: 24px; text-align: center; background: #ffffff;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 20px;">
                    <i class="bi bi-trash3"></i>
                </div>
                <h5 style="font-size: 15px; font-weight: 800; color: #1e293b; margin-bottom: 6px;">Delete Message?</h5>
                <p style="font-size: 12px; color: #64748b; margin-bottom: 18px; line-height: 1.4;">
                    As Super Admin, this message and attachments will be deleted permanently from the database and removed from all mobile & web screens instantly.
                </p>
                <div style="display: flex; gap: 8px;">
                    <button type="button" style="flex: 1; padding: 8px 12px; border-radius: 8px; border: none; background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 700; cursor: pointer;" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btnConfirmDeleteMsg" style="flex: 1; padding: 8px 12px; border-radius: 8px; border: none; background: #dc2626; color: #ffffff; font-size: 12px; font-weight: 700; cursor: pointer;">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- MODAL: Image Preview --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
            <div class="modal-body" style="padding: 0; text-align: center; position: relative;">
                <button type="button" class="btn-close btn-close-white" style="position: absolute; top: -35px; right: 0; filter: invert(1); z-index: 10;" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="previewModalImg" src="" style="max-width: 100%; max-height: 85vh; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); margin: 0 auto; display: block;" alt="Preview">
            </div>
        </div>
    </div>
</div>

{{-- Load Pusher JS --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
(function() {
    const PUSHER_KEY = @json($pusherKey ?? env('PUSHER_APP_KEY'));
    const PUSHER_CLUSTER = @json($pusherCluster ?? env('PUSHER_APP_CLUSTER', 'ap2'));
    const PANEL_PREFIX = @json($panel ?? 'super-admin');
    const IS_SUPERADMIN = @json(!empty($isSuperAdmin));
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const AUTH_USER = @json($authUser ?? null);
    const AUTH_USER_ID = AUTH_USER ? parseInt(AUTH_USER.id) : null;
    const AUTH_USER_SHORT_TYPE = AUTH_USER ? AUTH_USER.short_type : null;
    const AUTH_USER_CLASS = AUTH_USER ? AUTH_USER.class_name : null;

    let activeConversationId = null;
    let activePartyData = null;
    let isMonitorMode = false;
    let pendingDeleteMsgId = null;
    let selectedAttachmentFile = null;
    let activePusherChannel = null;
    let userPusherChannel = null;
    let allConversationsCache = [];
    const sentMessageIds = new Set();

    // DOM Elements
    const conversationsList = document.getElementById('conversationsList');
    const messagesContainer = document.getElementById('messagesContainer');
    const noChatSelected = document.getElementById('noChatSelected');
    const activeChatScreen = document.getElementById('activeChatScreen');
    const activePartyAvatar = document.getElementById('activePartyAvatar');
    const activePartyName = document.getElementById('activePartyName');
    const activePartyBadge = document.getElementById('activePartyBadge');
    const activePartyCode = document.getElementById('activePartyCode');
    const messageTextInput = document.getElementById('messageTextInput');
    const sendMessageForm = document.getElementById('sendMessageForm');
    const fileAttachmentInput = document.getElementById('fileAttachmentInput');
    const btnAttachFile = document.getElementById('btnAttachFile');
    const attachmentPreviewBanner = document.getElementById('attachmentPreviewBanner');
    const attachmentFileName = document.getElementById('attachmentFileName');
    const btnRemoveAttachment = document.getElementById('btnRemoveAttachment');
    const chatSearchInput = document.getElementById('chatSearchInput');
    const contactSearchInput = document.getElementById('contactSearchInput');
    const contactsListContainer = document.getElementById('contactsListContainer');
    const sidebarPane = document.getElementById('sidebarPane');
    const mainChatPane = document.getElementById('mainChatPane');

    // Clean up any lingering ?_token= query string from previous native form GET submits
    if (window.location.search.includes('_token=')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // -------------------------------------------------------------
    // Helper: Safe Element Visibility
    // -------------------------------------------------------------
    function setElementVisibility(el, visible, displayStyle = '') {
        if (!el) return;
        if (visible) {
            el.classList.remove('chat-d-none', 'tw-hidden', 'd-none', 'hidden');
            el.style.display = displayStyle || '';
        } else {
            el.classList.add('chat-d-none');
            el.style.display = 'none';
        }
    }

    // -------------------------------------------------------------
    // Pusher Initialization
    // -------------------------------------------------------------
    let pusher = null;
    if (PUSHER_KEY) {
        pusher = new Pusher(PUSHER_KEY, {
            cluster: PUSHER_CLUSTER,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            }
        });

        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher connected on cluster:', PUSHER_CLUSTER);
        });

        pusher.connection.bind('error', function(err) {
            console.error('❌ Pusher connection error:', err);
        });

        // Subscribe to user notification channel for real-time alerts across all chats
        if (AUTH_USER_ID && AUTH_USER_SHORT_TYPE) {
            const userChannelName = `private-user.${AUTH_USER_SHORT_TYPE}.${AUTH_USER_ID}`;
            userPusherChannel = pusher.subscribe(userChannelName);

            userPusherChannel.bind('pusher:subscription_succeeded', function() {
                console.log('✅ Subscribed to personal channel:', userChannelName);
            });

            userPusherChannel.bind('pusher:subscription_error', function(status) {
                console.warn('⚠️ Subscription error on personal channel:', userChannelName, status);
            });

            const handleUserMessage = function(payload) {
                console.log('⚡ Incoming message via personal channel:', payload);
                loadConversations();
                const msgConvId = payload.conversation_id || payload.chat_group_id || (payload.message && payload.message.conversation_id);
                if (activeConversationId && msgConvId && parseInt(activeConversationId) === parseInt(msgConvId)) {
                    processIncomingMessage(payload);
                }
            };

            userPusherChannel.bind('message.sent', handleUserMessage);
            userPusherChannel.bind('.message.sent', handleUserMessage);
            userPusherChannel.bind('App\\Events\\MessageSent', handleUserMessage);
        }
    } else {
        console.warn('⚠️ Pusher Key is missing in configuration.');
    }

    // -------------------------------------------------------------
    // Load Conversations List
    // -------------------------------------------------------------
    function loadConversations() {
        const url = isMonitorMode 
            ? `/${PANEL_PREFIX}/chat/all-conversations`
            : `/${PANEL_PREFIX}/chat/conversations`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if (!res.ok) {
                throw new Error(data?.message || `Server error (${res.status})`);
            }
            return data;
        })
        .then(data => {
            if (!data.success) {
                conversationsList.innerHTML = `<div style="padding: 24px; text-align: center; color: #ef4444; font-size: 12px;">${data.message || 'Error'}</div>`;
                return;
            }

            allConversationsCache = isMonitorMode ? (data.data?.data || []) : (data.data || []);
            filterAndRenderConversations(chatSearchInput ? chatSearchInput.value.trim() : '');
        })
        .catch(err => {
            conversationsList.innerHTML = `<div style="padding: 24px; text-align: center; color: #ef4444; font-size: 12px;">Failed to load conversations: ${err.message}</div>`;
        });
    }

    function filterAndRenderConversations(searchQuery = '') {
        let list = allConversationsCache;

        if (searchQuery) {
            const q = searchQuery.toLowerCase();
            list = list.filter(item => {
                const party = item.other_party || item.party_2 || {};
                const party1 = item.party_1 || {};
                const name = (party.name || '').toLowerCase();
                const code = (party.code || '').toLowerCase();
                const name1 = (party1.name || '').toLowerCase();
                const body = (item.last_message?.body || '').toLowerCase();
                return name.includes(q) || code.includes(q) || name1.includes(q) || body.includes(q);
            });
        }

        if (!list || list.length === 0) {
            conversationsList.innerHTML = `
                <div style="padding: 32px; text-align: center; color: #94a3b8; font-size: 12px;">
                    <i class="bi bi-chat-square-dots" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                    ${searchQuery ? 'No matching conversations.' : 'No conversations found.'}
                </div>
            `;
            return;
        }

        conversationsList.innerHTML = list.map(item => {
            const party = item.other_party || item.party_2 || {};
            const party1 = item.party_1;
            const subtitle = isMonitorMode && party1 
                ? `${party1.name} ↔ ${party.name}`
                : (item.last_message?.body || 'No messages yet');
            const time = item.last_message?.created_at || '';
            const isActive = (item.id === activeConversationId);
            const initial = (party.name || 'U').charAt(0).toUpperCase();

            return `
                <div class="chat-conv-item ${isActive ? 'active' : ''}" data-id="${item.id}" data-name="${escapeHtml(party.name || 'User')}" data-badge="${escapeHtml(party.badge || 'User')}" data-code="${escapeHtml(party.code || '')}">
                    <div class="chat-user-avatar" style="width: 38px; height: 38px; font-size: 13px;">
                        ${party.avatar ? `<img src="${party.avatar}" alt="Avatar">` : initial}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                            <h4 class="chat-conv-name">${escapeHtml(party.name || 'Unknown')}</h4>
                            <span class="chat-conv-time">${escapeHtml(time)}</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <p class="chat-conv-preview">${escapeHtml(subtitle)}</p>
                            ${item.unread_count > 0 ? `<span class="chat-conv-badge">${item.unread_count}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Attach click listener
        conversationsList.querySelectorAll('.chat-conv-item').forEach(el => {
            el.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                const name = this.getAttribute('data-name');
                const badge = this.getAttribute('data-badge');
                const code = this.getAttribute('data-code');
                openConversation(id, { name, badge, code });
            });
        });
    }

    if (chatSearchInput) {
        chatSearchInput.addEventListener('input', function() {
            filterAndRenderConversations(this.value.trim());
        });
    }

    // -------------------------------------------------------------
    // Open Conversation & Listen to Pusher Channel
    // -------------------------------------------------------------
    function openConversation(id, partyInfo) {
        activeConversationId = id;
        activePartyData = partyInfo;

        setElementVisibility(noChatSelected, false);
        setElementVisibility(activeChatScreen, true, 'flex');

        // On mobile, hide sidebar and show main chat pane
        if (window.innerWidth < 768) {
            setElementVisibility(sidebarPane, false);
            setElementVisibility(mainChatPane, true, 'flex');
        }

        activePartyName.textContent = partyInfo.name || 'Chat';
        activePartyBadge.textContent = partyInfo.badge || 'User';
        activePartyCode.textContent = partyInfo.code ? `#${partyInfo.code}` : '';
        activePartyAvatar.textContent = (partyInfo.name || 'U').charAt(0).toUpperCase();

        // Highlight active item in list
        conversationsList.querySelectorAll('.chat-conv-item').forEach(el => {
            if (parseInt(el.getAttribute('data-id')) === id) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        messagesContainer.innerHTML = `
            <div style="padding: 32px; text-align: center; color: #94a3b8; font-size: 12px;">
                <i class="bi bi-arrow-repeat spin-animation" style="font-size: 20px; display: block; margin-bottom: 8px;"></i>
                Loading messages...
            </div>
        `;

        // Subscribe to Pusher channel for this conversation
        subscribeToConversationChannel(id);

        // Fetch messages from backend
        fetch(`/${PANEL_PREFIX}/chat/conversations/${id}/messages`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if (!res.ok) {
                throw new Error(data?.message || `Server error (${res.status})`);
            }
            return data;
        })
        .then(data => {
            if (!data.success) {
                messagesContainer.innerHTML = `<div style="padding: 24px; text-align: center; color: #ef4444; font-size: 12px;">${data.message || 'Error'}</div>`;
                return;
            }
            renderMessages(data.data || []);
        })
        .catch(err => {
            messagesContainer.innerHTML = `<div style="padding: 24px; text-align: center; color: #ef4444; font-size: 12px;">Failed to load messages: ${err.message}</div>`;
        });
    }

    function subscribeToConversationChannel(conversationId) {
        if (!pusher) return;

        const channelName = `private-conversation.${conversationId}`;
        if (activePusherChannel === channelName) {
            return;
        }

        // Unsubscribe from previous conversation channel
        if (activePusherChannel) {
            try { pusher.unsubscribe(activePusherChannel); } catch (e) {}
        }

        activePusherChannel = channelName;
        const channel = pusher.subscribe(channelName);

        channel.bind('pusher:subscription_succeeded', function() {
            console.log('✅ Subscribed to conversation channel:', channelName);
        });

        channel.bind('pusher:subscription_error', function(status) {
            console.error('❌ Pusher subscription error on ' + channelName + ':', status);
        });

        // Unified Message Handler
        const onMessageSent = function(payload) {
            console.log('⚡ Real-time MessageSent received on ' + channelName + ':', payload);
            processIncomingMessage(payload);
            loadConversations();
        };

        channel.bind('message.sent', onMessageSent);
        channel.bind('.message.sent', onMessageSent);
        channel.bind('App\\Events\\MessageSent', onMessageSent);

        // Bind message deleted (Instant removal)
        const onMessageDeleted = function(payload) {
            console.log('⚡ Real-time MessageDeleted received:', payload);
            const deletedId = payload.messageId || payload.message_id || payload.id;
            const bubbleEl = document.getElementById(`msg-bubble-${deletedId}`);
            if (bubbleEl) {
                bubbleEl.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                bubbleEl.style.opacity = '0';
                bubbleEl.style.transform = 'scale(0.95)';
                setTimeout(() => bubbleEl.remove(), 250);
            }
        };

        channel.bind('message.deleted', onMessageDeleted);
        channel.bind('.message.deleted', onMessageDeleted);
        channel.bind('App\\Events\\MessageDeleted', onMessageDeleted);
    }

    function processIncomingMessage(payload) {
        if (!payload) return;

        const msgObj = (typeof payload === 'object' && payload !== null && typeof payload.message === 'object' && payload.message !== null)
            ? payload.message
            : payload;

        const msgId = msgObj.id ? parseInt(msgObj.id) : (payload.id ? parseInt(payload.id) : null);
        if (msgId && (document.getElementById(`msg-bubble-${msgId}`) || sentMessageIds.has(msgId))) {
            // Already present in DOM or already sent by this client session
            return;
        }

        // Extract body safely
        let bodyText = '';
        if (typeof msgObj.body === 'string' && msgObj.body.length > 0) {
            bodyText = msgObj.body;
        } else if (typeof msgObj.text === 'string' && msgObj.text.length > 0) {
            bodyText = msgObj.text;
        } else if (typeof payload.message === 'string' && payload.message.length > 0) {
            bodyText = payload.message;
        }

        // Extract sender identity
        const senderId = parseInt(msgObj.sender_id || payload.sender_id || 0);
        const senderType = msgObj.sender_type || payload.sender_type || '';
        const isMine = !!(AUTH_USER_ID && senderId === AUTH_USER_ID && 
            (!senderType || senderType === AUTH_USER_CLASS || senderType.toLowerCase().includes(AUTH_USER_SHORT_TYPE)));

        const senderName = payload.sender_name || msgObj.sender?.name || (isMine ? 'You' : (activePartyData?.name || 'User'));

        // Format attachments
        const rawAttachments = payload.attachments || msgObj.attachments || [];
        const formattedAttachments = rawAttachments.map(att => {
            const url = att.url || att.full_url || att.stream_url || (att.file_path ? `/storage/${att.file_path}` : '');
            return {
                id: att.id,
                file_name: att.file_name || 'Attachment',
                file_type: att.file_type || 'file',
                url: url
            };
        });

        // Format time
        let formattedTime = '';
        if (msgObj.created_at || payload.created_at) {
            try {
                formattedTime = new Date(msgObj.created_at || payload.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                formattedTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        } else {
            formattedTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        appendSingleMessage({
            id: msgId,
            body: bodyText,
            is_mine: isMine,
            can_delete: IS_SUPERADMIN,
            sender: { name: senderName },
            attachments: formattedAttachments,
            created_at: formattedTime
        });
    }

    function renderMessages(messages) {
        if (!messages || messages.length === 0) {
            messagesContainer.innerHTML = `
                <div style="padding: 32px; text-align: center; color: #94a3b8; font-size: 12px;">
                    <i class="bi bi-chat" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                    No messages yet. Send a message to start the conversation!
                </div>
            `;
            return;
        }

        messagesContainer.innerHTML = messages.map(msg => renderMessageHTML(msg)).join('');
        scrollToBottom();
    }

    function renderMessageHTML(msg) {
        const isMine = !!msg.is_mine;

        // Render attachments
        let attachmentsHTML = '';
        if (msg.attachments && msg.attachments.length > 0) {
            attachmentsHTML = msg.attachments.map(att => {
                const attUrl = att.url || att.full_url || att.stream_url || (att.file_path ? `/storage/${att.file_path}` : '#');
                if (att.file_type === 'image') {
                    return `
                        <div class="chat-att-img-wrap" onclick="openImageModal('${attUrl}')">
                            <img src="${attUrl}" class="chat-att-img" alt="Image">
                        </div>
                    `;
                } else {
                    return `
                        <a href="${attUrl}" target="_blank" download class="chat-att-file ${isMine ? 'mine' : 'theirs'}">
                            <i class="bi bi-file-earmark-arrow-down" style="font-size: 16px;"></i>
                            <span style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escapeHtml(att.file_name)}</span>
                        </a>
                    `;
                }
            }).join('');
        }

        // Delete button for SuperAdmin ONLY
        let deleteBtnHTML = '';
        if (IS_SUPERADMIN) {
            deleteBtnHTML = `
                <button type="button" class="btn-delete-msg" onclick="confirmDeleteMsg(${msg.id})" title="Delete message (Super Admin)">
                    <i class="bi bi-trash3"></i>
                </button>
            `;
        }

        return `
            <div class="chat-msg-row ${isMine ? 'mine' : 'theirs'}" id="msg-bubble-${msg.id}">
                ${isMine ? deleteBtnHTML : ''}
                <div class="chat-msg-bubble ${isMine ? 'mine' : 'theirs'}">
                    ${!isMine && msg.sender?.name ? `<div class="chat-msg-sender">${escapeHtml(msg.sender.name)}</div>` : ''}
                    ${msg.body ? `<div class="chat-msg-text">${escapeHtml(msg.body)}</div>` : ''}
                    ${attachmentsHTML}
                    <div class="chat-msg-meta">
                        <span>${escapeHtml(msg.created_at || '')}</span>
                        ${isMine ? '<i class="bi bi-check2-all" style="font-size: 13px;"></i>' : ''}
                    </div>
                </div>
                ${!isMine ? deleteBtnHTML : ''}
            </div>
        `;
    }

    function appendSingleMessage(msg) {
        if (!msg) return;
        const msgId = msg.id ? parseInt(msg.id) : null;
        if (msgId && document.getElementById(`msg-bubble-${msgId}`)) {
            return;
        }

        const placeholder = messagesContainer.querySelector('div[style*="padding: 32px"]');
        if (placeholder) placeholder.remove();

        const div = document.createElement('div');
        div.innerHTML = renderMessageHTML(msg);
        messagesContainer.appendChild(div.firstElementChild);
        scrollToBottom();
    }

    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const p = document.createElement('p');
        p.textContent = text;
        return p.innerHTML;
    }

    // -------------------------------------------------------------
    // Send Message
    // -------------------------------------------------------------
    function doSendMessage() {
        if (!activeConversationId) {
            alert('Please select a conversation from the list or start a new chat first.');
            return;
        }

        const text = messageTextInput.value.trim();
        if (!text && !selectedAttachmentFile) return;

        const btnSend = document.getElementById('btnSendMessage');
        if (btnSend.disabled) return;
        btnSend.disabled = true;

        const formData = new FormData();
        formData.append('body', text);
        if (selectedAttachmentFile) {
            formData.append('attachment', selectedAttachmentFile);
        }
        if (CSRF_TOKEN) {
            formData.append('_token', CSRF_TOKEN);
        }

        const reqHeaders = {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (pusher && pusher.connection && pusher.connection.socket_id) {
            reqHeaders['X-Socket-ID'] = pusher.connection.socket_id;
        }

        fetch(`/${PANEL_PREFIX}/chat/conversations/${activeConversationId}/messages`, {
            method: 'POST',
            headers: reqHeaders,
            body: formData,
            credentials: 'same-origin'
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;

            if (!res.ok) {
                const errorMsg = data?.message || (data?.errors ? Object.values(data.errors).flat().join(' ') : `Server error (${res.status}): ${res.statusText}`);
                throw new Error(errorMsg);
            }

            return data;
        })
        .then(data => {
            btnSend.disabled = false;

            if (!data || !data.success) {
                alert(data?.message || 'Failed to send message.');
                return;
            }

            // Track ID to prevent any duplicate appending from Pusher
            if (data.data && data.data.id) {
                const msgId = parseInt(data.data.id);
                sentMessageIds.add(msgId);
                appendSingleMessage(data.data);
            }

            messageTextInput.value = '';
            clearAttachment();
            loadConversations();
        })
        .catch(err => {
            btnSend.disabled = false;
            console.error('Send message error:', err);
            alert(err.message || 'Error sending message.');
        });
    }

    const btnSendMessage = document.getElementById('btnSendMessage');
    if (btnSendMessage) {
        btnSendMessage.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            doSendMessage();
        });
    }

    sendMessageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        doSendMessage();
        return false;
    });

    // Enter to send, Shift+Enter for newline
    messageTextInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            e.stopPropagation();
            doSendMessage();
            return false;
        }
    });

    // -------------------------------------------------------------
    // Attachments Handling
    // -------------------------------------------------------------
    btnAttachFile.addEventListener('click', () => fileAttachmentInput.click());

    fileAttachmentInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            selectedAttachmentFile = this.files[0];
            attachmentFileName.textContent = selectedAttachmentFile.name;
            setElementVisibility(attachmentPreviewBanner, true, 'flex');
        }
    });

    btnRemoveAttachment.addEventListener('click', clearAttachment);

    function clearAttachment() {
        selectedAttachmentFile = null;
        fileAttachmentInput.value = '';
        setElementVisibility(attachmentPreviewBanner, false);
    }

    // -------------------------------------------------------------
    // Delete Message (SuperAdmin Only)
    // -------------------------------------------------------------
    window.confirmDeleteMsg = function(msgId) {
        pendingDeleteMsgId = msgId;
        const modalEl = document.getElementById('deleteMsgModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    };

    const btnConfirmDeleteMsg = document.getElementById('btnConfirmDeleteMsg');
    if (btnConfirmDeleteMsg) {
        btnConfirmDeleteMsg.addEventListener('click', function() {
            if (!pendingDeleteMsgId) return;

            fetch(`/${PANEL_PREFIX}/chat/messages/${pendingDeleteMsgId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                const modalEl = document.getElementById('deleteMsgModal');
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();

                if (!data.success) {
                    alert(data.message || 'Could not delete message.');
                    return;
                }

                // Remove from DOM
                const bubble = document.getElementById(`msg-bubble-${pendingDeleteMsgId}`);
                if (bubble) bubble.remove();
            })
            .catch(err => alert('Delete error.'));
        });
    }

    // -------------------------------------------------------------
    // New Chat / Eligible Contacts Modal
    // -------------------------------------------------------------
    const btnNewChat = document.getElementById('btnNewChat');
    const heroBtn = document.querySelector('.btnStartNewChatHero');

    function openContactsModal() {
        const modal = new bootstrap.Modal(document.getElementById('newChatModal'));
        modal.show();
        loadContacts();
    }

    if (btnNewChat) btnNewChat.addEventListener('click', openContactsModal);
    if (heroBtn) heroBtn.addEventListener('click', openContactsModal);

    function loadContacts(search = '') {
        contactsListContainer.innerHTML = `
            <div style="text-align: center; padding: 32px; color: #94a3b8; font-size: 12px;">
                <i class="bi bi-arrow-repeat spin-animation" style="font-size: 20px; display: block; margin-bottom: 8px;"></i>
                Loading contacts...
            </div>
        `;

        fetch(`/${PANEL_PREFIX}/chat/contacts?search=${encodeURIComponent(search)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if (!res.ok) throw new Error(data?.message || `Error ${res.status}`);
            return data;
        })
        .then(data => {
            const contacts = data.data || [];
                if (contacts.length === 0) {
                    contactsListContainer.innerHTML = `<div style="text-align: center; padding: 32px; color: #94a3b8; font-size: 12px;">No eligible contacts found.</div>`;
                    return;
                }

                contactsListContainer.innerHTML = contacts.map(c => `
                    <div class="chat-contact-item" data-id="${c.id}" data-type="${escapeHtml(c.type)}" data-name="${escapeHtml(c.name)}" data-badge="${escapeHtml(c.badge)}" data-code="${escapeHtml(c.code || '')}">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="chat-user-avatar" style="width: 36px; height: 36px; font-size: 12px;">
                                ${c.avatar ? `<img src="${c.avatar}" alt="Avatar">` : (c.name || 'U').charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h4 style="font-size: 13px; font-weight: 800; color: #1e293b; margin: 0; line-height: 1.2;" class="dark:text-white">${escapeHtml(c.name)}</h4>
                                <span style="font-size: 11px; color: #64748b;">${c.code ? `#${escapeHtml(c.code)} • ` : ''}${escapeHtml(c.badge)}</span>
                            </div>
                        </div>
                        <i class="bi bi-chat-dots" style="color: var(--chat-primary); font-size: 18px;"></i>
                    </div>
                `).join('');

                contactsListContainer.querySelectorAll('.chat-contact-item').forEach(el => {
                    el.addEventListener('click', function() {
                        const receiverId = parseInt(this.getAttribute('data-id'));
                        const receiverType = this.getAttribute('data-type');
                        const name = this.getAttribute('data-name');
                        const badge = this.getAttribute('data-badge');
                        const code = this.getAttribute('data-code');

                        startConversationWith(receiverId, receiverType, { name, badge, code });
                    });
                });
            });
    }

    function startConversationWith(receiverId, receiverType, partyInfo) {
        fetch(`/${PANEL_PREFIX}/chat/conversations`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                _token: CSRF_TOKEN,
                receiver_id: receiverId,
                receiver_type: receiverType
            }),
            credentials: 'same-origin'
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if (!res.ok) {
                const msg = data?.message || (data?.errors ? Object.values(data.errors).flat().join(' ') : `Error ${res.status}`);
                throw new Error(msg);
            }
            return data;
        })
        .then(data => {
            const modalEl = document.getElementById('newChatModal');
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();

            if (!data || !data.success) {
                alert(data?.message || 'Could not start conversation.');
                return;
            }

            loadConversations();
            openConversation(data.data.id, partyInfo);
        })
        .catch(err => {
            alert(err.message || 'Could not start conversation.');
        });
    }

    // Search contacts filter
    let searchDebounce = null;
    contactSearchInput.addEventListener('input', function() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            loadContacts(this.value.trim());
        }, 300);
    });

    // -------------------------------------------------------------
    // SuperAdmin Tab Toggle (My Chats vs Monitor All)
    // -------------------------------------------------------------
    const tabMyChats = document.getElementById('tabMyChats');
    const tabAllChats = document.getElementById('tabAllChats');
    if (tabMyChats && tabAllChats) {
        tabMyChats.addEventListener('click', function() {
            isMonitorMode = false;
            tabMyChats.classList.add('active');
            tabAllChats.classList.remove('active');
            loadConversations();
        });

        tabAllChats.addEventListener('click', function() {
            isMonitorMode = true;
            tabAllChats.classList.add('active');
            tabMyChats.classList.remove('active');
            loadConversations();
        });
    }

    // Mobile back button
    document.getElementById('btnBackToSidebar').addEventListener('click', function() {
        setElementVisibility(mainChatPane, false);
        setElementVisibility(sidebarPane, true, 'flex');
    });

    // Handle window resize for mobile / desktop breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            setElementVisibility(sidebarPane, true, 'flex');
            setElementVisibility(mainChatPane, true, 'flex');
        } else {
            if (activeConversationId) {
                setElementVisibility(sidebarPane, false);
                setElementVisibility(mainChatPane, true, 'flex');
            } else {
                setElementVisibility(sidebarPane, true, 'flex');
                setElementVisibility(mainChatPane, false);
            }
        }
    });

    // Image preview modal helper
    window.openImageModal = function(url) {
        document.getElementById('previewModalImg').src = url;
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    };

    // Initial load
    loadConversations();

})();
</script>
