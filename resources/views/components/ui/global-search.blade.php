@props([
    'placeholder' => 'Search anything…',
    'shortcut'    => 'Ctrl+K',
    'modules'     => [],   // e.g. ['invoices', 'customers', 'products']
])

<div x-data="{
    open: false,
    query: '',
    results: [],
    loading: false,
    init() {
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.open = !this.open;
                if (this.open) this.$nextTick(() => this.$refs.input.focus());
            }
            if (e.key === 'Escape') this.open = false;
        });
    },
    search() {
        if (this.query.length < 2) { this.results = []; return; }
        this.loading = true;
        fetch(`{{ url('/api/search') }}?q=${encodeURIComponent(this.query)}`)
            .then(r => r.json())
            .then(d => { this.results = d.results || []; this.loading = false; });
    }
}" class="relative">
    <button
        type="button"
        @click="open = true; $nextTick(() => $refs.input.focus())"
        class="hidden md:flex items-center gap-3 w-72 px-3 py-2 bg-ink-50 hover:bg-ink-100 border border-ink-200 rounded-xl text-sm text-ink-500 transition-colors"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <span>{{ $placeholder }}</span>
        <kbd class="ms-auto text-xs px-1.5 py-0.5 rounded bg-white border border-ink-200 text-ink-600 font-mono">{{ $shortcut }}</kbd>
    </button>

    <button
        type="button"
        @click="open = true; $nextTick(() => $refs.input.focus())"
        class="md:hidden btn btn--ghost btn--icon"
        aria-label="Search"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </button>

    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        class="modal-backdrop"
        :class="{ 'is-open': open }"
        @click="open = false"
        x-cloak
    ></div>

    <div
        x-show="open"
        x-transition.duration.200ms
        class="modal"
        :class="{ 'is-open': open }"
        x-cloak
        @click.stop
    >
        <div class="modal__panel max-w-2xl w-full p-0 overflow-hidden">
            <div class="relative">
                <svg class="absolute top-1/2 -translate-y-1/2 start-4 w-5 h-5 text-ink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="search"
                    x-ref="input"
                    x-model="query"
                    @input.debounce.300ms="search()"
                    placeholder="{{ $placeholder }}"
                    class="w-full ps-12 pe-12 py-4 bg-transparent text-base border-b border-ink-200 focus:outline-none focus:border-navy-600"
                />
                <kbd class="absolute top-1/2 -translate-y-1/2 end-4 text-xs px-2 py-1 rounded bg-ink-100 text-ink-600 font-mono">ESC</kbd>
            </div>

            <div class="max-h-96 overflow-y-auto p-2">
                <template x-if="loading">
                    <div class="p-6 text-center text-sm text-ink-500">Searching…</div>
                </template>

                <template x-if="!loading && query.length >= 2 && results.length === 0">
                    <div class="p-12 text-center">
                        <div class="empty-state__icon mx-auto">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m9-7a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="empty-state__title">No results found</div>
                        <div class="empty-state__desc">Try different keywords or check spelling.</div>
                    </div>
                </template>

                <template x-if="!loading && query.length < 2">
                    <div class="p-4">
                        <div class="section__title">Quick links</div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="/dashboard" class="quick-action">
                                <div class="quick-action__icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8a2 2 0 002 2h2a2 2 0 002-2v-8m-6 0h6"/></svg>
                                </div>
                                <div>
                                    <div class="quick-action__title">Dashboard</div>
                                </div>
                            </a>
                            <a href="/invoices" class="quick-action">
                                <div class="quick-action__icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <div class="quick-action__title">Invoices</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </template>

                <template x-for="result in results" :key="result.id">
                    <a :href="result.url" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-ink-50">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-navy-50 text-navy-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-ink-900 truncate" x-text="result.title"></div>
                            <div class="text-xs text-ink-500 truncate" x-text="result.subtitle"></div>
                        </div>
                        <x-ui.tag variant="navy" x-text="result.module"></x-ui.tag>
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>
