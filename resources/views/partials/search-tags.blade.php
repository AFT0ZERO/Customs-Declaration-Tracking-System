<form method="GET" action="{{ $action }}" class="mt-3 mt-sm-0 w-100" id="searchForm">
    <!-- Main Search Bar -->
    <div class="d-flex gap-2 w-100 flex-column flex-sm-row">
        <div class="d-flex flex-wrap align-items-center gap-2 p-1 border rounded bg-white w-100" id="tagInputContainer">
            <!-- tags will be injected here via JS -->
            <input type="text" id="tagInput" class="border-0 flex-grow-1 p-1" style="outline: none; min-width: 150px;"
                placeholder="بحث عن بيانات... (افصل بمسافة)">
        </div>

        <input type="hidden" name="search" id="hiddenSearchInput" value="{{ $searchValue ?? request('search') }}">
        <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
        <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning py-2 px-4 shadow-sm" title="بحث">
                <i class="bi bi-search"></i>
            </button>
            <button type="button" class="btn btn-danger py-2 px-3 shadow-sm" id="clearTagsBtn" title="مسح الكل">
                <i class="bi bi-trash"></i>
            </button>
            <button type="button" class="btn btn-secondary py-2 px-3 shadow-sm" data-bs-toggle="collapse"
                data-bs-target="#advancedFilters"
                aria-expanded="{{ !empty($filters) && (count(array_filter($filters)) > 0) ? 'true' : 'false' }}"
                aria-controls="advancedFilters" title="فلترة متقدمة">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    </div>

    <!-- Advanced Filters Collapse -->
    @php
        $hasActiveFilters = isset($filters) && count(array_filter($filters)) > 0;
    @endphp
    <div class="collapse mt-3 {{ $hasActiveFilters ? 'show' : '' }}" id="advancedFilters">
        <div class="card card-body bg-light border-0 shadow-sm">
            <div class="row g-3">

                <!-- Status Filter -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold small text-muted mb-2">الحالة الحالية</label>
                    @php
                        $selectedStatuses = request('statuses', []);
                        $availableStatuses = [
                            'عمان لغايات الفحص',
                            'العقبة ساحة 4 غذاء',
                            'العقبة غذاء البلد',
                            'العقبة مكتب 4',
                            'العقبة الارشيف',
                            'عمان التاجر',
                            'عمان'
                        ];
                    @endphp
                    <div class="border rounded p-2 bg-white" style="max-height: 140px; overflow-y: auto;">
                        @foreach($availableStatuses as $status)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="statuses[]" value="{{ $status }}"
                                    id="status_{{ $loop->index }}" {{ in_array($status, $selectedStatuses) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="status_{{ $loop->index }}">
                                    {{ $status }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Declaration Type (Center) Filter -->
                <div class="col-md-6 col-lg-3">
                    <label class="form-label fw-bold small text-muted mb-2">مركز البيان</label>
                    @php
                        $selectedTypes = request('types', []);
                        $availableTypes = [
                            '220',
                            '224',
                            '900',
                        ];
                    @endphp
                    <div class="border rounded p-2 bg-white" style="max-height: 140px; overflow-y: auto;">
                        @foreach($availableTypes as $type)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="types[]" value="{{ $type }}"
                                    id="type_{{ $loop->index }}" {{ in_array($type, $selectedTypes) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="type_{{ $loop->index }}">
                                    {{ $type }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Date Range Filters -->
                <div class="col-md-12 col-lg-6">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">تاريخ الإضافة</label>
                            <div class="input-group">
                                <span class="input-group-text">من</span>
                                <input type="date" name="created_from" class="form-control"
                                    value="{{ request('created_from') }}">
                                <span class="input-group-text">إلى</span>
                                <input type="date" name="created_to" class="form-control"
                                    value="{{ request('created_to') }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">تاريخ التعديل</label>
                            <div class="input-group">
                                <span class="input-group-text">من</span>
                                <input type="date" name="updated_from" class="form-control"
                                    value="{{ request('updated_from') }}">
                                <span class="input-group-text">إلى</span>
                                <input type="date" name="updated_to" class="form-control"
                                    value="{{ request('updated_to') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ $action }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> مسح الفلاتر
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> تطبيق الفلترة
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tagInput = document.getElementById('tagInput');
        const hiddenSearchInput = document.getElementById('hiddenSearchInput');
        const tagInputContainer = document.getElementById('tagInputContainer');
        const searchForm = document.getElementById('searchForm');

        // Extract rendered declaration numbers from table
        const renderedDeclarationNumbers = [];
        document.querySelectorAll('table tbody tr').forEach(row => {
            const numCell = row.querySelector('td:nth-child(3)');
            if (numCell && numCell.textContent.trim() !== '') {
                renderedDeclarationNumbers.push(numCell.textContent.trim());
            }
        });

        let tags = [];

        // Helper to normalize number the same way backend does to check equality safely
        function normalize(num) {
            let str = String(num).trim();
            if (str.length > 17) {
                let n = str.substring(17);
                let res = "";
                for (let i = 0; i < n.length; i++) {
                    if (n[i] !== '0') res += n[i];
                }
                return res;
            }
            return str;
        }

        const normalizedRenderedNumbers = renderedDeclarationNumbers.map(n => normalize(n));

        function renderTags() {
            // remove existing chips but keep the input
            tagInputContainer.querySelectorAll('.badge').forEach(chip => chip.remove());

            tags.forEach((tag, index) => {
                const chip = document.createElement('div');
                // Using Bootstrap badge classes
                chip.className = 'badge rounded-pill bg-light text-dark d-flex align-items-center gap-2 p-2 px-3';
                chip.style.fontSize = '14px';
                chip.style.fontWeight = 'normal';

                const originalSearch = "{{ $searchValue ?? request('search') }}";
                if (originalSearch.trim() !== '') {
                    if (!normalizedRenderedNumbers.includes(normalize(tag))) {
                        chip.classList.remove('bg-light', 'text-dark');
                        chip.classList.add('bg-danger', 'text-white');
                        chip.title = 'لا توجد نتائج';
                    }
                }

                const textSpan = document.createElement('span');
                textSpan.textContent = tag;

                const removeBtn = document.createElement('span');
                removeBtn.className = 'ms-1 cursor-pointer';
                removeBtn.style.cursor = 'pointer';
                removeBtn.style.fontSize = '18px';
                removeBtn.style.lineHeight = '1';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = function () {
                    tags.splice(index, 1);
                    updateHiddenInput();
                    renderTags();
                }

                chip.appendChild(textSpan);
                chip.appendChild(removeBtn);

                tagInputContainer.insertBefore(chip, tagInput);
            });
        }

        function updateHiddenInput() {
            hiddenSearchInput.value = tags.join(' ');
        }

        function addTagsFromInput(value) {
            const newTags = value.split(/[\s,]+/).filter(t => t.trim() !== '');
            if (newTags.length > 0) {
                newTags.forEach(t => {
                    if (!tags.includes(t)) {
                        tags.push(t);
                    }
                });
                updateHiddenInput();
                renderTags();
            }
        }

        function clearAllTags() {
            tags = [];
            updateHiddenInput();
            renderTags();
            tagInput.value = '';
        }

        // Initialize from hidden input
        if (hiddenSearchInput.value.trim() !== '') {
            addTagsFromInput(hiddenSearchInput.value);
        }

        // Event listener for clear all button
        document.getElementById('clearTagsBtn').addEventListener('click', clearAllTags);

        // Events
        tagInput.addEventListener('keydown', function (e) {
            if (e.key === ' ') {
                e.preventDefault();
                addTagsFromInput(this.value);
                this.value = '';
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    addTagsFromInput(this.value);
                    this.value = '';
                }
                searchForm.submit();
            } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
                tags.pop();
                updateHiddenInput();
                renderTags();
            }
        });

        tagInput.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text');
            addTagsFromInput(pasteData);
        });

        searchForm.addEventListener('submit', function (e) {
            if (tagInput.value.trim() !== '') {
                addTagsFromInput(tagInput.value);
                tagInput.value = '';
            }
        });

        tagInputContainer.addEventListener('click', function (e) {
            if (e.target === tagInputContainer) {
                tagInput.focus();
            }
        });
    });
</script>