<?php
$c = file_get_contents("e:/public_html/resources/views/super-admin/work-order/bulk-allocate.blade.php");
$old = '<div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="d-flex justify-content-between">';
$new = '<div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="craftsman_due_date" class="form-label">Craftsman Due Date</label>
                                    <input type="date" class="form-control @error(\'craftsman_due_date\') is-invalid @enderror" 
                                           id="craftsman_due_date" name="craftsman_due_date" value="{{ old(\'craftsman_due_date\') }}">
                                    @error(\'craftsman_due_date\')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-flex justify-content-between">';
// CRLF conversion just in case
$old_crlf = str_replace("\n", "\r\n", $old);
$new_crlf = str_replace("\n", "\r\n", $new);

$c = str_replace($old_crlf, $new_crlf, $c);
$c = str_replace($old, $new, $c); // Fallback

file_put_contents("e:/public_html/resources/views/super-admin/work-order/bulk-allocate.blade.php", $c);
echo "Replaced in super-admin/bulk-allocate.blade.php\n";
