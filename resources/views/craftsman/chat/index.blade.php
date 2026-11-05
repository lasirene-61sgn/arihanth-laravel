@extends('craftsman.layouts.app')

@section('title', 'Craftsman Chat')

@section('content')
<div class="chat-page-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-1 text-slate-800 dark:text-white" style="font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                Craftsman Live Chat
            </h1>
            <p class="text-muted mb-0" style="font-size: 12px;">
                Instant real-time messaging with staff and order managers
            </p>
        </div>
    </div>

    @include('chat.chat-ui')
</div>
@endsection
