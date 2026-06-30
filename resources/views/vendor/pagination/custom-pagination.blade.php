@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="tw-flex tw-items-center tw-justify-between tw-py-4">
        <div class="tw-flex tw-flex-1 tw-justify-between sm:tw-hidden">
            @if ($paginator->onFirstPage())
                <span class="tw-relative tw-inline-flex tw-items-center tw-rounded-md tw-border tw-border-gray-300 tw-bg-white tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-300 tw-cursor-default">
                    @lang('pagination.previous')
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="tw-relative tw-inline-flex tw-items-center tw-rounded-md tw-border tw-border-gray-300 tw-bg-white tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    @lang('pagination.previous')
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="tw-relative tw-ml-3 tw-inline-flex tw-items-center tw-rounded-md tw-border tw-border-gray-300 tw-bg-white tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    @lang('pagination.next')
                </a>
            @else
                <span class="tw-relative tw-ml-3 tw-inline-flex tw-items-center tw-rounded-md tw-border tw-border-gray-300 tw-bg-white tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-300 tw-cursor-default">
                    @lang('pagination.next')
                </span>
            @endif
        </div>

        <div class="tw-hidden sm:tw-flex sm:tw-flex-1 sm:tw-items-center sm:tw-justify-between">
            <div class="tw-flex tw-items-center tw-gap-2">
                <p class="tw-text-xs tw-text-gray-500">
                    Showing
                    <span class="tw-font-bold">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="tw-font-bold">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="tw-font-bold">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div class="tw-flex tw-items-center">
                <nav class="tw-isolate tw-inline-flex tw-space-x-1 tw-rounded-md" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="tw-relative tw-inline-flex tw-items-center tw-rounded-lg tw-p-1.5 tw-text-gray-300 tw-cursor-default" aria-hidden="true">
                            <i class="bi bi-chevron-left tw-text-lg"></i>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tw-relative tw-inline-flex tw-items-center tw-rounded-lg tw-p-1.5 tw-text-gray-500 hover:tw-bg-gray-100 hover:tw-text-gray-700 tw-transition-colors" aria-label="@lang('pagination.previous')">
                            <i class="bi bi-chevron-left tw-text-lg"></i>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="tw-relative tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-text-sm tw-font-medium tw-text-gray-400 tw-cursor-default">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="tw-relative tw-z-10 tw-inline-flex tw-items-center tw-rounded-lg tw-bg-primary tw-px-3.5 tw-py-1.5 tw-text-sm tw-font-bold tw-text-white focus:tw-z-20">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="tw-relative tw-inline-flex tw-items-center tw-rounded-lg tw-px-3.5 tw-py-1.5 tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-100 hover:tw-text-gray-700 tw-transition-colors focus:tw-z-20">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tw-relative tw-inline-flex tw-items-center tw-rounded-lg tw-p-1.5 tw-text-gray-500 hover:tw-bg-gray-100 hover:tw-text-gray-700 tw-transition-colors" aria-label="@lang('pagination.next')">
                            <i class="bi bi-chevron-right tw-text-lg"></i>
                        </a>
                    @else
                        <span class="tw-relative tw-inline-flex tw-items-center tw-rounded-lg tw-p-1.5 tw-text-gray-300 tw-cursor-default" aria-hidden="true">
                            <i class="bi bi-chevron-right tw-text-lg"></i>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </nav>
@endif