<!-- Unlock Duration Modal -->
<div id="unlockModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800">Unlock Designs for Viewing</h3>
            <button onclick="hideUnlockModal()" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="p-6">
            <p class="text-sm text-slate-500 mb-4">Configure access for Buyers, Key Users, Users, and Craftsmen.</p>

            <!-- Category Selection (for Bulk Unlock) -->
            <div id="categorySelectionSection" class="mb-4 hidden p-4 bg-violet-50 rounded-lg border border-violet-100">
                <label class="block text-xs font-bold text-violet-700 uppercase mb-2">Select Category or Subcategory to Unlock</label>
                
                <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto p-2">
                    @foreach($categories ?? collect() as $category)
                        <div class="border border-violet-200 rounded-xl p-3 bg-white hover:border-violet-400 transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" class="unlock-category-checkbox text-violet-600 focus:ring-violet-500" data-category="{{ $category->id }}" onchange="toggleSubcategories({{ $category->id }}, this.checked); updateUnlockSelectionLabel()">
                                    <div class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                                        <i class="bi bi-folder-fill text-sm"></i>
                                    </div>
                                    <span class="font-bold text-slate-800 text-sm">{{ $category->name }}</span>
                                    <span class="text-xs text-slate-500">({{ $category->products_count }})</span>
                                </div>
                            </div>
                            @if($category->subcategories->count() > 0)
                                <div class="ml-4 pl-4 border-l-2 border-violet-100 space-y-1">
                                    @foreach($category->subcategories as $sub)
                                        <div class="flex items-center justify-between text-xs text-slate-600">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" class="unlock-subcategory-checkbox text-violet-600 focus:ring-violet-500" data-category="{{ $category->id }}" data-subcategory="{{ $sub->id }}" onchange="updateUnlockSelectionLabel()">
                                                <i class="bi bi-arrow-return-right text-violet-300"></i>
                                                <span class="font-medium">{{ $sub->name }}</span>
                                                <span class="text-xs text-slate-400">({{ $sub->products_count }})</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p class="text-[10px] text-violet-400 font-bold mt-2 uppercase">This will apply to all locked designs in the selected categories or subcategories.</p>
                <div id="selectedUnlockNodeLabel" class="text-xs font-bold text-violet-700 mt-1"></div>
            </div>
            
            <!-- Duration Unit Selector -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Duration Unit</label>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <label class="flex items-center justify-center px-1 py-2 border-2 border-indigo-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition has-[:checked]:bg-indigo-500 has-[:checked]:text-white has-[:checked]:border-indigo-500">
                        <input type="radio" name="durationUnit" value="minutes" class="sr-only" checked>
                        <span class="text-xs font-semibold">Mins</span>
                    </label>
                    <label class="flex items-center justify-center px-1 py-2 border-2 border-indigo-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition has-[:checked]:bg-indigo-500 has-[:checked]:text-white has-[:checked]:border-indigo-500">
                        <input type="radio" name="durationUnit" value="hours" class="sr-only">
                        <span class="text-xs font-semibold">Hrs</span>
                    </label>
                    <label class="flex items-center justify-center px-1 py-2 border-2 border-indigo-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition has-[:checked]:bg-indigo-500 has-[:checked]:text-white has-[:checked]:border-indigo-500">
                        <input type="radio" name="durationUnit" value="weeks" class="sr-only">
                        <span class="text-xs font-semibold">Wks</span>
                    </label>
                    <label class="flex items-center justify-center px-1 py-2 border-2 border-indigo-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition has-[:checked]:bg-indigo-500 has-[:checked]:text-white has-[:checked]:border-indigo-500">
                        <input type="radio" name="durationUnit" value="months" class="sr-only">
                        <span class="text-xs font-semibold">Mths</span>
                    </label>
                    <label class="flex items-center justify-center px-1 py-2 border-2 border-indigo-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition has-[:checked]:bg-indigo-500 has-[:checked]:text-white has-[:checked]:border-indigo-500">
                        <input type="radio" name="durationUnit" value="years" class="sr-only">
                        <span class="text-xs font-semibold">Yrs</span>
                    </label>
                    <label class="flex items-center justify-center px-1 py-2 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-50 transition has-[:checked]:bg-red-500 has-[:checked]:text-white has-[:checked]:border-red-500">
                        <input type="radio" name="durationUnit" value="permanent" class="sr-only">
                        <span class="text-xs font-semibold">Perm</span>
                    </label>
                </div>
            </div>

            <!-- Duration Dropdown -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Duration</label>
                <select id="unlockDuration" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <!-- Populated by JavaScript -->
                </select>
            </div>

            <!-- User Scope Selector -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Access Scope</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center justify-center px-4 py-2 border-2 border-emerald-200 rounded-lg cursor-pointer hover:bg-emerald-50 transition has-[:checked]:bg-emerald-500 has-[:checked]:text-white has-[:checked]:border-emerald-500">
                        <input type="radio" name="userScope" value="all" class="sr-only" checked>
                        <span class="text-sm font-semibold">All Users</span>
                    </label>
                    <label class="flex items-center justify-center px-4 py-2 border-2 border-emerald-200 rounded-lg cursor-pointer hover:bg-emerald-50 transition has-[:checked]:bg-emerald-500 has-[:checked]:text-white has-[:checked]:border-emerald-500">
                        <input type="radio" name="userScope" value="specific" class="sr-only">
                        <span class="text-sm font-semibold">Specific Users</span>
                    </label>
                </div>
            </div>

            <!-- User Selection -->
            <div id="userSelectionContainer" class="mb-4 hidden">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Select Users</label>
                
                <div class="relative group">
                    <!-- Dropdown Trigger -->
                    <button type="button" onclick="toggleUserDropdown()" id="userDropdownTrigger" class="w-full bg-white border border-slate-200 rounded-lg p-2.5 text-sm flex justify-between items-center text-left hover:border-indigo-400 focus:ring-2 focus:ring-indigo-500 transition">
                        <span id="userSelectLabel" class="text-slate-400">Select Users...</span>
                        <i class="bi bi-chevron-down text-slate-400"></i>
                    </button>

                    <!-- Dropdown Panel -->
                    <div id="userDropdownPanel" class="hidden absolute z-20 w-full bg-white border border-slate-200 rounded-lg mt-1 shadow-xl max-h-64 overflow-hidden flex flex-col">
                        <!-- Search Header -->
                        <div class="p-2 border-b border-slate-100 bg-slate-50 flex gap-2">
                            <input type="text" id="userSearchInput" class="w-full border border-slate-200 rounded-md p-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none" placeholder="Search users...">
                            <button type="button" onclick="loadAvailableUsers(true)" class="px-2 py-1 bg-slate-200 text-slate-600 rounded hover:bg-slate-300" title="Refresh User List">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        
                        <!-- List Container -->
                        <div id="userListContainer" class="overflow-y-auto p-1 flex-1">
                            <p class="text-xs text-slate-400 text-center py-2">Loading users...</p>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1">Click to open selection</p>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="hideUnlockModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition">Cancel</button>
            <button onclick="confirmUnlock()" id="confirmUnlockBtn" class="px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Confirm Unlock</button>
        </div>
    </div>
</div>

<script>
    let designsToUnlock = [];
    let usersLoaded = false;
    let isLoadingUsers = false;

    function showUnlockModal(mode = 'selected') {
        window.currentUnlockType = mode;
        const categorySection = document.getElementById('categorySelectionSection');
        const userScopeSection = document.getElementById('userSelectionContainer');
        
        if (mode === 'category') {
            if(categorySection) categorySection.classList.remove('hidden');
            designsToUnlock = []; // Not needed for category mode
        } else {
            if(categorySection) categorySection.classList.add('hidden');
            const checkboxes = document.querySelectorAll('.design-checkbox:checked');
            if(checkboxes.length > 0) {
                designsToUnlock = Array.from(checkboxes).map(cb => cb.value);
            } else {
                designsToUnlock = [];
                alert('Please select at least one design to unlock.');
                return;
            }
        }
        
        document.getElementById('unlockModal').classList.remove('hidden');

        // Initialize duration options for minutes (default) - force reset
        try {
            const defaultUnit = document.querySelector('input[name="durationUnit"][value="minutes"]');
            if(defaultUnit) defaultUnit.checked = true;
            updateDurationOptions('minutes');
            
            // Reset user scope to all
            const allScope = document.querySelector('input[name="userScope"][value="all"]');
            if(allScope) {
                allScope.checked = true;
                if(userScopeSection) userScopeSection.classList.add('hidden');
            }
        } catch(e) {
            console.error('Error resetting modal state:', e);
        }
        
        // Load available users if not already loaded
        if (!usersLoaded && !isLoadingUsers) {
            loadAvailableUsers();
        }
    }

    function hideUnlockModal() {
        document.getElementById('unlockModal').classList.add('hidden');
    }

    // Duration options by unit
    const durationOptions = {
        minutes: [
            {value: 1, label: '1 Minute'},
            {value: 5, label: '5 Minutes'},
            {value: 15, label: '15 Minutes'},
            {value: 30, label: '30 Minutes'},
            {value: 45, label: '45 Minutes'}
        ],
        hours: [
            {value: 1, label: '1 Hour'},
            {value: 2, label: '2 Hours'},
            {value: 4, label: '4 Hours'},
            {value: 8, label: '8 Hours'},
            {value: 12, label: '12 Hours'},
            {value: 24, label: '24 Hours (1 Day)'},
            {value: 48, label: '48 Hours (2 Days)'},
            {value: 168, label: '168 Hours (1 Week)'}
        ],
        weeks: [
            {value: 1, label: '1 Week'},
            {value: 2, label: '2 Weeks'},
            {value: 3, label: '3 Weeks'},
            {value: 4, label: '4 Weeks (1 Month)'}
        ],
        months: [
            {value: 1, label: '1 Month'},
            {value: 2, label: '2 Months'},
            {value: 3, label: '3 Months'},
            {value: 6, label: '6 Months'}
        ],
        years: [
            {value: 1, label: '1 Year'}
        ],
        permanent: [
            {value: -1, label: 'Permanent'}
        ]
    };

    // Update duration dropdown based on selected unit
    function updateDurationOptions(unit) {
        // Safe access to unit, ensuring it's lowercase string
        const safeUnit = String(unit).toLowerCase();
        
        const select = document.getElementById('unlockDuration');
        if(!select) return;
        select.innerHTML = '';
        
        if (safeUnit === 'permanent') {
            const option = document.createElement('option');
            option.value = -1;
            option.textContent = 'Permanent Unlock';
            option.selected = true;
            select.appendChild(option);
            select.disabled = true;
        } else {
            select.disabled = false;
            // Check if key exists in object
            if(Object.prototype.hasOwnProperty.call(durationOptions, safeUnit)) {
                durationOptions[safeUnit].forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    select.appendChild(option);
                });
            } else {
                 console.warn('Unknown duration unit:', safeUnit);
            }
        }
    }

    // Toggle Dropdown
    function toggleUserDropdown() {
        const panel = document.getElementById('userDropdownPanel');
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            const input = document.getElementById('userSearchInput');
            if(input) input.focus();
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const container = document.getElementById('userSelectionContainer');
        const panel = document.getElementById('userDropdownPanel');
        // Only if container exists and click is outside
        if (container && !container.contains(event.target) && panel && !panel.classList.contains('hidden')) {
            panel.classList.add('hidden');
        }
    });

    // Filtering logic
    const searchInput = document.getElementById('userSearchInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const container = document.getElementById('userListContainer');
            const items = container.querySelectorAll('.user-item');
            const headers = container.querySelectorAll('.group-header');
    
            items.forEach(item => {
                const text = item.textContent || item.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
    
            // Hide empty groups
            headers.forEach(header => {
                let hasVisible = false;
                let sibling = header.nextElementSibling;
                while (sibling && !sibling.classList.contains('group-header')) {
                    if (!sibling.classList.contains('hidden')) {
                        hasVisible = true;
                        break;
                    }
                    sibling = sibling.nextElementSibling;
                }
                if (hasVisible) {
                    header.classList.remove('hidden');
                } else {
                    header.classList.add('hidden');
                }
            });
        });
    }

    function updateUserSelectionLabel() {
        const checked = document.querySelectorAll('.user-checkbox:checked');
        const count = checked.length;
        const label = document.getElementById('userSelectLabel');
        const trigger = document.getElementById('userDropdownTrigger');
        
        if (count === 0) {
            label.textContent = 'Select Users...';
            label.classList.add('text-slate-400');
            label.classList.remove('text-slate-800', 'font-semibold');
            trigger.classList.remove('border-indigo-500', 'ring-1', 'ring-indigo-500');
        } else {
            label.textContent = `${count} User${count !== 1 ? 's' : ''} Selected`;
            label.classList.remove('text-slate-400');
            label.classList.add('text-slate-800', 'font-semibold');
            trigger.classList.add('border-indigo-500', 'ring-1', 'ring-indigo-500');
        }
    }

    // Load available users from server
    async function loadAvailableUsers(forceRefetch = false) {
        if(isLoadingUsers && !forceRefetch) return;
        isLoadingUsers = true;
        
        const container = document.getElementById('userListContainer');
        if(!container) return;
        
        console.log('Starting loadAvailableUsers...');
        container.innerHTML = '<div class="p-4 text-center text-xs text-slate-500"><i class="bi bi-arrow-repeat animate-spin text-indigo-500 text-xl block mb-2"></i>Loading users...</div>';

        try {
            const response = await fetch("{{ route('super-admin.design.get-available-users') }}");
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const users = await response.json();
            console.log('Users loaded:', users);
            
            container.innerHTML = '';
            
            const createGroup = (title, items, typePrefix) => {
                if (!items || items.length === 0) return;
                
                // Group Header
                const header = document.createElement('div');
                header.className = 'group-header px-2 py-1 bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky top-0';
                header.textContent = title;
                container.appendChild(header);
                
                // Items
                items.forEach(user => {
                    const div = document.createElement('label');
                    div.className = 'user-item flex items-center px-2 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0';
                    
                    // Determine correct code field based on type
                    let code = 'N/A';
                    let name = 'Unknown';
                    
                    if (typePrefix === 'buyer') {
                        code = user.bp_code;
                        name = user.business_name || user.name;
                    } else if (typePrefix === 'craftsman') {
                        code = user.craftman_code;
                        name = user.name;
                    } else {
                        // Key User or User
                        code = user.user_code || user.key;
                        name = user.full_name;
                    }

                    // Fallback to empty string if null to avoid "null" text
                    code = code || '';
                    name = name || '';

                    const val = `${typePrefix}:${code}`;
                    
                    div.innerHTML = `
                        <input type="checkbox" class="user-checkbox rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2" value="${val}">
                        <div class="text-sm flex flex-col">
                            <span class="font-bold text-indigo-600 font-mono text-xs">${code}</span>
                            <span class="text-slate-600">${name}</span>
                        </div>
                    `;
                    
                    // Add change listener to update label
                    div.querySelector('input').addEventListener('change', updateUserSelectionLabel);
                    
                    container.appendChild(div);
                });
            };

            let hasUsers = false;
            
            if (users.buyers && users.buyers.length > 0) { createGroup('Buyers', users.buyers, 'buyer'); hasUsers = true; }
            if (users.key_users && users.key_users.length > 0) { createGroup('Key Users', users.key_users, 'key_user'); hasUsers = true; }
            if (users.users && users.users.length > 0) { createGroup('Users', users.users, 'user'); hasUsers = true; }
            if (users.craftsmen && users.craftsmen.length > 0) { createGroup('Craftsmen', users.craftsmen, 'craftsman'); hasUsers = true; }
            
            if (!hasUsers) {
                container.innerHTML = '<div class="p-4 text-center text-xs text-slate-500">No users found available for selection.</div>';
            }
            
            usersLoaded = true;
            
        } catch (error) {
            console.error('Error loading users:', error);
            container.innerHTML = `<div class="p-4 text-center text-xs text-red-500">
                <i class="bi bi-exclamation-triangle text-xl block mb-2"></i>
                Failed to load users.<br>
                <button type="button" onclick="loadAvailableUsers(true)" class="mt-2 text-indigo-600 underline">Retry</button>
            </div>`;
        } finally {
            isLoadingUsers = false;
        }
    }

    async function confirmUnlock() {
        // Use the designsToUnlock variable populated in showUnlockModal
        const unlockType = window.currentUnlockType || 'selected';
        let selected = unlockType === 'selected' ? designsToUnlock : null;
        let categoryIds = [];
        let subcategoryIds = [];
        
        if (unlockType === 'category') {
            categoryIds = Array.from(document.querySelectorAll('.unlock-category-checkbox:checked')).map(cb => cb.getAttribute('data-category'));
            subcategoryIds = Array.from(document.querySelectorAll('.unlock-subcategory-checkbox:checked')).map(cb => cb.getAttribute('data-subcategory'));
            
            if (categoryIds.length === 0 && subcategoryIds.length === 0) {
                alert('Please select at least one category or subcategory.');
                return;
            }
        }

        if (unlockType === 'selected' && (!selected || selected.length === 0)) {
            alert('No designs selected.');
            return;
        }

        let duration = parseInt(document.getElementById('unlockDuration').value);
        const durationUnit = document.querySelector('input[name="durationUnit"]:checked').value;
        const userScope = document.querySelector('input[name="userScope"]:checked').value;
        const btn = document.getElementById('confirmUnlockBtn');
        
        // Force valid integer for permanent unlock
        if (durationUnit === 'permanent') {
            duration = -1;
        }

        // Get selected users if scope is specific
        let selectedUsers = null;
        if (userScope === 'specific') {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            selectedUsers = Array.from(checkboxes).map(cb => cb.value);
            
            if (selectedUsers.length === 0) {
                alert('Please select at least one user.');
                toggleUserDropdown(); // Open dropdown to show where to select
                return;
            }
        }
        
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin me-2"></i>Processing...';

        const payload = {
            unlock_type: unlockType,
            duration: duration,
            duration_unit: durationUnit,
            user_scope: userScope,
            selected_users: selectedUsers
        };

        if (unlockType === 'selected') {
            payload.ids = selected;
        } else {
            payload.category_ids = categoryIds;
            payload.subcategory_ids = subcategoryIds;
        }

        try {
            const response = await fetch("{{ route('super-admin.design.unlock-designs') }}", {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert(result.message || 'Designs unlocked successfully');
                window.location.reload();
            } else {
                alert('Gagal: ' + (result.message || 'Unknown error occurred'));
            }
        } catch (error) {
            console.error('Unlock Error:', error);
            alert('Terjadi kesalahan pada server. Silahkan periksa koneksi atau hubungi administrator.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Confirm Permissions';
        }
        hideUnlockModal();
    }

    function updateUnlockSelectionLabel() {
        const selectedCategories = document.querySelectorAll('.unlock-category-checkbox:checked').length;
        const selectedSubcategories = document.querySelectorAll('.unlock-subcategory-checkbox:checked').length;
        const label = document.getElementById('selectedUnlockNodeLabel');
        if (!label) return;
        
        if (selectedCategories > 0 || selectedSubcategories > 0) {
            label.textContent = `Selected: ${selectedCategories} Categories, ${selectedSubcategories} Subcategories`;
        } else {
            label.textContent = '';
        }
    }

    function toggleSubcategories(categoryId, checked) {
        document.querySelectorAll(`.unlock-subcategory-checkbox[data-category="${categoryId}"]`).forEach(cb => {
            cb.checked = checked;
        });
    }

    // Event Listeners for Modal Interactions
    function initUnlockModalEvents() {
        console.log('Initializing Unlock Modal Events');
        
        // Duration Unit - Delegate if possible or attach directly
        const unitRadios = document.querySelectorAll('input[name="durationUnit"]');
        unitRadios.forEach(radio => {
            radio.removeEventListener('change', handleDurationUnitChange);
            radio.addEventListener('change', handleDurationUnitChange);
        });

        // User Scope
        const scopeRadios = document.querySelectorAll('input[name="userScope"]');
        scopeRadios.forEach(radio => {
            radio.removeEventListener('change', handleUserScopeChange);
            radio.addEventListener('change', handleUserScopeChange);
        });
    }
    
    function handleDurationUnitChange(e) {
        updateDurationOptions(this.value);
    }
    
    function handleUserScopeChange(e) {
        const container = document.getElementById('userSelectionContainer');
        if (this.value === 'specific') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUnlockModalEvents);
    } else {
        initUnlockModalEvents();
    }
</script>
