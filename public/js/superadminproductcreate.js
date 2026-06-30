
    function initSearchableDropdown(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const display = container.querySelector('.custom-dropdown-display');
        const menu = container.querySelector('.custom-dropdown-menu');
        const searchInput = container.querySelector('.custom-dropdown-search');
        const list = container.querySelector('.custom-dropdown-list');
        const hiddenSelect = container.querySelector('select');

        function updateDisplay() {
            const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
            if (selectedOption && selectedOption.value !== "") {
                display.textContent = selectedOption.textContent;
            } else {
                // ADDED: Logic to show "Select Craftsman"
                const id = hiddenSelect.getAttribute('id');
                if (id === 'bp_code') display.textContent = 'Select BP Code';
                else if (id === 'craftsman_code') display.textContent = 'Select Craftsman'; // ADDED
                else if (id === 'product_category_id') display.textContent = 'Select Category';
                else display.textContent = 'Select Sub Category';
            }
        }

        function filterOptions() {
            const filter = searchInput.value.toLowerCase();
            const items = list.querySelectorAll('.custom-dropdown-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        function selectOption(value, text) {
            hiddenSelect.value = value;
            hiddenSelect.dispatchEvent(new Event('change'));
            updateDisplay();
            menu.style.display = 'none';
            searchInput.value = '';
            filterOptions();
        }

        function refreshList() {
            list.innerHTML = '';
            Array.from(hiddenSelect.options).forEach(option => {
                if (option.value === "") return;
                const item = document.createElement('div');
                item.className = 'custom-dropdown-item';
                item.textContent = option.textContent;
                item.dataset.value = option.value;
                item.addEventListener('click', () => selectOption(option.value, option.textContent));
                list.appendChild(item);
            });
            updateDisplay();
        }

        display.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = menu.style.display === 'block';
            document.querySelectorAll('.custom-dropdown-menu').forEach(m => m.style.display = 'none');
            menu.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) searchInput.focus();
        });

        searchInput.addEventListener('input', filterOptions);

        document.addEventListener('click', () => {
            menu.style.display = 'none';
        });

        menu.addEventListener('click', (e) => e.stopPropagation());

        refreshList();

        return {
            refresh: refreshList,
            select: (value, text) => {
                if (!Array.from(hiddenSelect.options).some(o => o.value == value)) {
                    const opt = document.createElement('option');
                    opt.value = value; opt.textContent = text;
                    hiddenSelect.appendChild(opt);
                    refreshList();
                }
                selectOption(value, text);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const bpCodeDropdown = initSearchableDropdown('dropdown_container_bp_code');
        const craftsmanDropdown = initSearchableDropdown('dropdown_container_craftsman_code'); 
        const categoryDropdown = initSearchableDropdown('dropdown_container_product_category_id');
        const subcategoryDropdown = initSearchableDropdown('dropdown_container_subcategory_id');

        const categorySelect = document.getElementById('product_category_id');
        const subcategoryContainer = document.getElementById('subcategory-container');
        const subcategorySelect = document.getElementById('subcategory_id');
        const optionBlocks = document.querySelectorAll('.category-option');

        function refreshCategoryOptions(categoryId) {
            optionBlocks.forEach(b => b.style.display = 'none');
            if (!categoryId) return;
            fetch(`${window.ProductAppConfig.getCategoryOptionsUrl}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(data => {
                    Object.keys(data).forEach(key => {
                        if (data[key]) {
                            const el = document.querySelector(`.category-option[data-opt="${key}"]`);
                            if (el) el.style.display = '';
                        }
                    });
                });
        }

        function refreshSubcategories(categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
            
            if (!categoryId) { 
                subcategoryContainer.style.display = 'none'; 
                subcategoryDropdown.refresh();
                return; 
            }
            
            subcategoryContainer.style.display = ''; 

            fetch(`${window.ProductAppConfig.getSubcategoriesUrl}?category_id=${categoryId}`)
                .then(r => r.json())
                .then(list => {
                    list.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id; 
                        opt.textContent = s.name; 
                        subcategorySelect.appendChild(opt);
                    });
                    subcategoryDropdown.refresh();
                });
        }

        categorySelect.addEventListener('change', function() {
            refreshSubcategories(this.value);
            refreshCategoryOptions(this.value);
        });

        if (categorySelect.value) {
            refreshSubcategories(categorySelect.value);
            refreshCategoryOptions(categorySelect.value);
        }

        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            const name = prompt('Enter new category name');
            if (!name) return;
            const opts = {
                has_hook: confirm('Enable Hook?') ? 1 : 0,
                has_enamel: confirm('Enable Enamel?') ? 1 : 0,
                has_rodium: confirm('Enable Rodium?') ? 1 : 0,
                has_open_close: confirm('Enable Open/Close?') ? 1 : 0,
                has_stone: confirm('Enable Stone?') ? 1 : 0,
            };
            fetch(window.ProductAppConfig.storeCategoryUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.ProductAppConfig.csrfToken },
                body: JSON.stringify({ name, ...opts })
            }).then(r => r.json()).then(res => {
                if (res.category) {
                    categoryDropdown.select(res.category.id, res.category.name);
                    refreshCategoryOptions(res.category.id);
                    subcategoryContainer.style.display = '';
                }
            });
        });

        document.getElementById('addSubcategoryBtn').addEventListener('click', function() {
            const parentId = categorySelect.value;
            if (!parentId) return alert('Select category first');
            const name = prompt('Enter new subcategory name');
            if (!name) return;
            fetch(window.ProductAppConfig.storeCategoryUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.ProductAppConfig.csrfToken },
                body: JSON.stringify({ parent_category_id: parentId, name })
            }).then(r => r.json()).then(res => {
                if (res.subcategory) {
                    subcategoryDropdown.select(res.subcategory.id, res.subcategory.name);
                }
            });
        });
    });

    
