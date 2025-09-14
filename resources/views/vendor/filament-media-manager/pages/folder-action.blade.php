@php
    $isDisabled = method_exists($action, 'isDisabled') ? $action->isDisabled() : false;
    $url = method_exists($action, 'getUrl') ? $action->getUrl() : null;
    $shouldPostToUrl = method_exists($action, 'shouldPostToUrl') ? $action->shouldPostToUrl() : false;
    $wireClick = method_exists($action, 'getLivewireClickHandler') ? $action->getLivewireClickHandler() : null;
    $alpineClick = method_exists($action, 'getAlpineClickHandler') ? $action->getAlpineClickHandler() : null;
@endphp

@if ($url && ! $shouldPostToUrl)
    <a href="{{ $url }}" @if($alpineClick) x-on:click="{{ $alpineClick }}" @endif target="{{ method_exists($action,'shouldOpenUrlInNewTab') && $action->shouldOpenUrlInNewTab() ? '_blank' : null }}"
       class="fi-ac-btn-action inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition focus-visible:ring-2 disabled:opacity-70"
       @if($isDisabled) aria-disabled="true" @endif>
        @include('vendor.filament-media-manager.pages.partials.folder-card', ['item' => $item])
    </a>
@else
    <button type="button"
            @if($wireClick) wire:click="{{ $wireClick }}" @endif
            @if($alpineClick) x-on:click="{{ $alpineClick }}" @endif
            @if($isDisabled) disabled @endif
            class="fi-ac-btn-action inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition focus-visible:ring-2 disabled:opacity-70">
        @include('vendor.filament-media-manager.pages.partials.folder-card', ['item' => $item])
    </button>
@endif

@once
    @push('styles')
        <style>
            .folder-icon-{{ $item->id }} {
                width: 100px;
                height: 70px;
                background-color: {{ $item->color ?? '#f3c623' }};
                border-radius: 5px;
                position: relative;
                margin: 20px 10px 10px 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .folder-icon-{{ $item->id }}::before {
                content: "";
                width: 40px;
                height: 10px;
                background-color: {{ $item->color ?? '#f3c623' }};
                border-radius: 5px 5px 0 0;
                position: absolute;
                top: -10px;
                left: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        </style>
    @endpush
@endonce
