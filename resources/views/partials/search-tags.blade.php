<form method="GET" action="{{ $action }}" class="d-flex mt-3 mt-sm-0 gap-2 w-100 flex-column flex-sm-row" id="searchForm">
    <div class="d-flex flex-wrap align-items-center gap-2 p-1 border rounded bg-white w-100" id="tagInputContainer">
        <!-- tags will be injected here via JS -->
        <input type="text" id="tagInput" class="border-0 flex-grow-1 p-1" style="outline: none; min-width: 150px;" placeholder="بحث عن بيانات... (افصل بمسافة)">
    </div>
    <input type="hidden" name="search" id="hiddenSearchInput" value="{{ $searchValue ?? request('search') }}">
    <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
    <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
    <button type="submit" class="btn btn-warning py-2">بحث</button>
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
        if(numCell && numCell.textContent.trim() !== '') {
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
            removeBtn.onclick = function() {
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
                if(!tags.includes(t)) {
                    tags.push(t);
                }
            });
            updateHiddenInput();
            renderTags();
        }
    }

    // Initialize from hidden input
    if (hiddenSearchInput.value.trim() !== '') {
        addTagsFromInput(hiddenSearchInput.value);
    }

    // Events
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            addTagsFromInput(this.value);
            this.value = '';
        } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
            tags.pop();
            updateHiddenInput();
            renderTags();
        }
    });

    tagInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text');
        addTagsFromInput(pasteData);
    });

    searchForm.addEventListener('submit', function(e) {
        if (tagInput.value.trim() !== '') {
            addTagsFromInput(tagInput.value);
            tagInput.value = '';
        }
    });
    
    tagInputContainer.addEventListener('click', function(e) {
        if (e.target === tagInputContainer) {
            tagInput.focus();
        }
    });
});
</script>

