@props(['placeholder' => 'بحث عن بيان...', 'route' => 'dashboard', 'sortKey' => 'created_at', 'sortDirection' => 'desc'])

<div class="search-tags-wrapper">
    <div class="search-tags-input-container">
        <input 
            type="text" 
            class="search-tags-input" 
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            data-route="{{ route($route) }}"
            data-sort="{{ $sortKey }}"
            data-direction="{{ $sortDirection }}"
        >
        <div class="search-tags-container"></div>
    </div>
    <button type="button" class="btn btn-warning search-tags-submit">بحث</button>
</div>

<style>
    .search-tags-wrapper {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .search-tags-input-container {
        flex: 1;
        position: relative;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        min-height: 38px;
        padding: 0.25rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background-color: #fff;
        align-content: flex-start;
    }

    .search-tags-input-container:focus-within {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .search-tags-input {
        flex: 1;
        min-width: 150px;
        border: none;
        outline: none;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        background: transparent;
    }

    .search-tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        width: 100%;
    }

    .search-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        background-color: #e9ecef;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        white-space: nowrap;
        animation: slideIn 0.2s ease-out;
    }

    .search-tag.no-results {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .search-tag .tag-text {
        font-weight: 500;
    }

    .search-tag .tag-remove {
        cursor: pointer;
        font-weight: bold;
        font-size: 1.2rem;
        line-height: 1;
        opacity: 0.7;
        transition: opacity 0.2s ease;
        padding: 0;
        border: none;
        background: none;
        color: inherit;
    }

    .search-tag .tag-remove:hover {
        opacity: 1;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .search-tags-submit {
        align-self: center;
        height: 38px;
        padding: 0.375rem 1rem;
    }

    @media (max-width: 576px) {
        .search-tags-wrapper {
            flex-direction: column;
            gap: 0.75rem;
        }

        .search-tags-input-container {
            min-height: auto;
        }

        .search-tags-submit {
            width: 100%;
            align-self: stretch;
        }

        .search-tags-input {
            min-width: 100px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('.search-tags-input');
        const tagsContainer = document.querySelector('.search-tags-container');
        const inputContainer = document.querySelector('.search-tags-input-container');
        const tags = new Set();

        function createTag(value) {
            const tagSpan = document.createElement('span');
            tagSpan.className = 'search-tag';
            tagSpan.dataset.value = value;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'tag-remove';
            removeBtn.innerHTML = '×';
            removeBtn.title = 'Remove this search term';

            const textSpan = document.createElement('span');
            textSpan.className = 'tag-text';
            textSpan.textContent = value;

            tagSpan.appendChild(textSpan);
            tagSpan.appendChild(removeBtn);

            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                tags.delete(value);
                tagSpan.remove();
                searchInput.focus();
            });

            return tagSpan;
        }

        function addTag(value) {
            if (value.trim() && !tags.has(value.trim())) {
                tags.add(value.trim());
                tagsContainer.appendChild(createTag(value.trim()));
            }
        }

        searchInput.addEventListener('keydown', (e) => {
            if ((e.key === ' ' || e.key === 'Enter') && searchInput.value.trim()) {
                e.preventDefault();
                addTag(searchInput.value);
                searchInput.value = '';
            }

            if (e.key === 'Backspace' && !searchInput.value && tags.size > 0) {
                const lastTag = tagsContainer.lastElementChild;
                if (lastTag) {
                    const value = lastTag.dataset.value;
                    tags.delete(value);
                    lastTag.remove();
                }
            }
        });

        searchInput.addEventListener('blur', () => {
            if (searchInput.value.trim()) {
                addTag(searchInput.value);
                searchInput.value = '';
            }
        });

        // Submit form with tags
        const submitBtn = document.querySelector('.search-tags-submit');
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const baseRoute = searchInput.dataset.route;
            const sort = searchInput.dataset.sort;
            const direction = searchInput.dataset.direction;

            if (tags.size === 0) {
                // If no tags, just reload the page
                window.location.href = `${baseRoute}?sort=${sort}&direction=${direction}`;
                return;
            }

            // Build search query with all tags
            const searchParams = new URLSearchParams();
            const tagArray = Array.from(tags);
            searchParams.append('search', tagArray.join(' '));
            searchParams.append('sort', sort);
            searchParams.append('direction', direction);

            window.location.href = `${baseRoute}?${searchParams.toString()}`;
        });

        // Handle existing search terms (if page reloads with search query)
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        if (searchParam) {
            const terms = searchParam.split(' ').filter(t => t.trim());
            terms.forEach(term => {
                tags.add(term);
                tagsContainer.appendChild(createTag(term));
            });
        }
    });
</script>
