@extends('buyer.layouts.app')

@section('title', 'Messages & Chat')

@section('content')
<div class="chat-page-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-1 text-slate-800 dark:text-white" style="font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                Messages & Live Chat
            </h1>
            <p class="text-muted mb-0" style="font-size: 12px;">
                Instant real-time communication with administrators and business team
            </p>
        </div>
    </div>

    @include('chat.chat-ui')
</div>
@endsection
