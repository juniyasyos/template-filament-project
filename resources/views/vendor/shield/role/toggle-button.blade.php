<div class="flex items-center gap-3">
    <x-filament::input.checkbox data-checkall="" />
    <span id="checkall-label" class="font-medium text-sm">
        {{ __('Select all permissions') }}
    </span>
    <a href="https://github.com/juniyasyos/shield-docs" target="_blank" class="text-sm text-primary-600 hover:underline">
        {{ __('Docs') }}
    </a>
</div>
<div class="text-xs text-gray-500 mt-1">
    {{ __('Tip: Use the bulk toggle in each section or tab to quickly grant or revoke access.') }}
</div>
