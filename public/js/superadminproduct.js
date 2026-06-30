   function toggleSection(sectionId) {
        ['searchSection', 'filterSection', 'sortSection'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = (id === sectionId && el.style.display === 'none') ? 'block' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const saved = localStorage.getItem('productTableColumns');
        if (saved) {
            const hidden = JSON.parse(saved);
            document.querySelectorAll('.column-checkbox').forEach(cb => {
                if (hidden.includes(cb.value)) { cb.checked = false; applyVis(cb.value, false); }
            });
        }
    });

    document.querySelectorAll('.column-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            applyVis(this.value, this.checked);
            const hidden = Array.from(document.querySelectorAll('.column-checkbox:not(:checked)')).map(c => c.value);
            localStorage.setItem('productTableColumns', JSON.stringify(hidden));
        });
    });

    function applyVis(col, isVis) {
        document.querySelectorAll('.' + col).forEach(el => el.style.display = isVis ? '' : 'none');
    }

    function resetAll() {
        localStorage.removeItem('productTableColumns');
        window.location.href = "{{ route('super-admin.product.index') }}";
    }

    function toggleSelectAll(checked) {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
    }

    function printSelectedProducts() {
        const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one product to print.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('super-admin.product.print-selected') }}";
        form.target = '_blank';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_products[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }