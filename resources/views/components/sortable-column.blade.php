@props(['label', 'sortKey'])
<th>
    {{ $label }}
    <div class="btn-group btn-group-sm">
        <a href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => 'asc']) }}"
            class="btn btn-sm {{ request('sort') === $sortKey && request('direction') === 'asc' ? 'btn-success' : '' }}">
            <i class="bi bi-arrow-up"></i>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => 'desc']) }}"
            class="btn btn-sm {{ request('sort') === $sortKey && request('direction') === 'desc' ? 'btn-success' : '' }}">
            <i class="bi bi-arrow-down"></i>
        </a>
    </div>
</th>
