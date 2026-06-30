<?php
$path = 'e:/public_html/resources/views/super-admin/purchase-order/index.blade.php';
$lines = file($path);
// find MODALS
$start = -1;
foreach ($lines as $i => $line) {
    if (strpos($line, '{{-- MODALS --}}') !== false) {
        $start = $i;
        break;
    }
}

if ($start !== -1) {
    // we just truncate and append
    $newLines = array_slice($lines, 0, $start + 1);
    
    $modals = <<<'EOD'
<div class="modal fade" id="bulkAllocateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('super-admin.purchase-order.bulk-allocate') }}" method="POST">
            @csrf
            <div id="selected-ids-container"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.bulk_allocate_orders') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.select_craftsman') }}</label>
                        <select name="craftsman_code" class="form-select select2-bulk" required>
                            <option value="">{{ __('messages.select_craftsman') }}</option>
                            @foreach($craftsmen as $c)
                            <option value="{{ $c->craftman_code }}">{{ $c->craftman_code }} - {{ $c->business_name }} {{ $c->dear ? '('.$c->dear.')' : '' }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">This will move selected orders to "Allocated" status.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Craftsman Due Date</label>
                        <input type="date" name="craftsman_due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('messages.allocate_now') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="bulkApproveForm" action="{{ route('super-admin.purchase-order.bulk-approve') }}" method="POST" style="display:none;">
    @csrf
    <div id="bulk-approve-ids"></div>
</form>

<form id="bulkCompleteForm" action="{{ route('super-admin.purchase-order.bulk-complete') }}" method="POST" style="display:none;">
    @csrf
    <div id="bulk-complete-ids"></div>
</form>

<!-- Bulk Print Form (Hidden) -->
<form id="bulkPrintForm" action="{{ route('super-admin.purchase-order.bulk-print') }}" method="POST" target="_blank">
    @csrf
    <div id="print-ids-container"></div>
</form>
EOD;

    $c = implode("", $newLines) . $modals . "\n";
    
    // now we need to grab the javascript which starts after the print form. Wait, I deleted the JS?
    // Oh no, the original file has lots of JS after MODALS!
}
