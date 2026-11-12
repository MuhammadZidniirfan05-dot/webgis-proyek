{{-- Sidebar komputer --}}
<aside class="hidden bg-indigo-950 lg:block lg:w-64 lg:shrink-0">
    <div class="sticky top-0 h-screen">
        @include('layouts._sidebar')
    </div>
</aside>

{{-- Drawer ponsel --}}
<div x-show="menu" style="display: none;" class="fixed inset-0 z-40 flex lg:hidden"
     role="dialog" aria-modal="true" aria-label="Menu" @keydown.escape.window="menu = false">
    <div class="fixed inset-0 bg-slate-900/50" @click="menu = false"></div>
    <div class="relative h-full w-72 max-w-[85%] bg-indigo-950">
        <button type="button" @click="menu = false" aria-label="Tutup menu"
                class="absolute right-2 top-3 rounded-md p-2 text-indigo-200 hover:bg-indigo-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300">
            <x-icon name="x" class="h-5 w-5" />
        </button>
        @include('layouts._sidebar')
    </div>
</div>