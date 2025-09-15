<div wire:poll="{{ $this->interval() }}">
	{{ $this->table }}

	<!-- Required for Filament v4 actions/tables modals -->
	<x-filament-actions::modals />
</div>
