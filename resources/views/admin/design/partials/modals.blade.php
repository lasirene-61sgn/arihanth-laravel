<!-- Accept Design Modal -->
<div id="acceptDesignModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h5 class="text-lg font-bold text-gray-900">Accept Design</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('acceptDesignModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="acceptDesignForm" method="POST">
            @csrf
            <div class="p-6 text-center">
                <i class="bi bi-question-circle text-5xl text-emerald-500 mb-4 block"></i>
                <p class="text-sm text-gray-600">Are you sure you want to accept this design? <br>A unique Design Code will be generated automatically for Product: <strong id="acceptProductCode" class="text-magenta-800"></strong></p>
            </div>
            <div class="p-6 bg-gray-50 flex gap-3">
                <button type="button" class="flex-1 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-100 transition-colors" onclick="closeModal('acceptDesignModal')">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200">Accept Now</button>
            </div>
        </form>
    </div>
</div>

<!-- Unlock Duration Modal -->
<div id="unlockModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg my-8 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-indigo-50">
            <div>
                <h5 class="text-lg font-bold text-indigo-900">Unlock Design Access</h5>
                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mt-0.5">Configure access permissions</p>
            </div>
            <button type="button" class="text-indigo-400 hover:text-indigo-600" onclick="closeModal('unlockModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
            <!-- Selected Designs Preview -->
            <div id="selectedDesignsPreview" class="space-y-2 p-4 bg-gray-50 rounded-xl border border-gray-100" style="display: none;">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Selected Items</label>
                <div id="previewImagesContainer" class="flex flex-wrap gap-2"></div>
            </div>

            <!-- Category Selection -->
            <div id="categorySelectionSection" class="space-y-2 p-4 bg-violet-50 rounded-xl border border-violet-100" style="display: none;">
                <label class="text-xs font-bold text-violet-700 uppercase tracking-wider block mb-2">Select Category or Subcategory to Unlock</label>
                
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
            
            <!-- Duration Settings -->
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-3">1. Select Duration Unit</label>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach(['minutes' => 'Min', 'hours' => 'Hrs', 'months' => 'Mths', 'years' => 'Yrs', 'permanent' => 'Perm'] as $unit => $lbl)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="durationUnit" value="{{ $unit }}" class="peer hidden" {{ $unit == 'minutes' ? 'checked' : '' }}>
                                <div class="px-2 py-3 bg-white border border-gray-200 rounded-xl text-center transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 group-hover:border-indigo-300">
                                    <span class="text-xs font-bold">{{ $lbl }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div id="durationAmountWrapper">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">2. Set Precise Duration</label>
                    <select id="unlockDuration" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500"></select>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- User Scope -->
            <div class="space-y-4">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">3. Define Access Scope</label>
                <div class="flex gap-3">
                    <label class="flex-1 relative cursor-pointer group">
                        <input type="radio" name="userScope" value="all" class="peer hidden" checked>
                        <div class="px-4 py-3 bg-white border border-gray-200 rounded-xl text-center transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 group-hover:border-indigo-300">
                            <span class="text-sm font-bold">Public: All Users</span>
                        </div>
                    </label>
                    <label class="flex-1 relative cursor-pointer group">
                        <input type="radio" name="userScope" value="specific" class="peer hidden">
                        <div class="px-4 py-3 bg-white border border-gray-200 rounded-xl text-center transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 group-hover:border-indigo-300">
                            <span class="text-sm font-bold">Private: Specific</span>
                        </div>
                    </label>
                </div>

                <div id="userSelectionContainer" class="space-y-3 animate-fadeIn" style="display: none;">
                    <div class="relative group">
                        <input type="text" id="userSearchInput" 
                            placeholder="Search by Name, BP Code, or User Code..." 
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-inner">
                        <select id="selectedUsers" multiple class="w-full h-48 p-2 text-sm focus:outline-none border-0 divide-y divide-gray-50 select-multiple-custom">
                        </select>
                    </div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users</p>
                </div>
            </div>
        </div>
        
        <div class="p-6 bg-gray-50 flex gap-4 border-t border-gray-100">
            <button type="button" class="flex-1 px-4 py-3 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-100 transition-colors" onclick="closeModal('unlockModal')">Cancel</button>
            <button type="button" onclick="confirmUnlock()" id="confirmUnlockBtn" class="flex-1 px-4 py-3 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">Confirm Permissions</button>
        </div>
    </div>
</div>
