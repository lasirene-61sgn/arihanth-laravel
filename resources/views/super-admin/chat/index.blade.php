@extends('super-admin.layouts.app')

@section('title', 'Live Chat & Messaging')

@section('content')
<div class="tw-p-4 md:tw-p-6">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
        <div>
            <h1 class="tw-text-xl tw-font-black tw-tracking-wide tw-text-slate-800 dark:tw-text-white tw-uppercase tw-mb-1" style="letter-spacing: 0.5px;">
                Live Chat & Messaging
            </h1>
            <p class="tw-text-xs tw-text-slate-500 dark:tw-text-slate-400 tw-m-0">
                Instant communication synchronized in real-time with the Mobile App via Pusher
            </p>
        </div>
    </div>

    @include('chat.chat-ui')
</div>
@endsection
