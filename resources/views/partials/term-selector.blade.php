<div class="inline-flex items-center gap-2 rounded-xl border border-line bg-card-2 px-3 py-1.5">
    <label for="academic-term-selector" class="text-xs font-semibold uppercase text-muted">Term</label>
    <select id="academic-term-selector" aria-label="Choose academic term"
        onchange="window.location.assign(this.value)"
        class="max-w-40 cursor-pointer bg-transparent text-sm font-semibold text-ink focus-visible:outline-2 focus-visible:outline-blue">
        <option value="{{ request()->fullUrlWithQuery(['term' => 'all']) }}" @selected($selectedTerm === 'all')>All terms</option>
        @foreach ($terms as $index => $term)
            <option value="{{ request()->fullUrlWithQuery(['term' => (string) $index]) }}" @selected($selectedTerm === (string) $index)>
                {{ $term }}
            </option>
        @endforeach
    </select>
</div>
