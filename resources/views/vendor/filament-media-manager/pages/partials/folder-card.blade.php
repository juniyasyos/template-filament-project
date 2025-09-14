<div class="flex flex-col justify-center items-center gap-4">
    <div class="folder-icon-{{ $item->id }} flex flex-col items-center justify-center">
        <x-icon :name="$item->icon ?: 'heroicon-o-folder'" class="text-white w-8 h-8" />
    </div>
    <div class="flex flex-col items-center justify-center my-2">
        <div>
            <h1 class="font-bold text-xl">{{ $item->name }}</h1>
        </div>

        <div class="flex justify-start mt-1">
            <p class="text-gray-600 dark:text-gray-300 text-sm truncate ...">
                {{ $item->created_at->diffForHumans() }}
            </p>
        </div>
    </div>
</div>

