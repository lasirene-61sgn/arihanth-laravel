<?php
$path = 'e:/public_html/app/Models/PurchaseOrder.php';
$c = file_get_contents($path);

// Find the getCreatorDetailsAttribute
$search = '            } elseif ($this->creator_type === "admin") {
                $processOwner = \App\Models\ProcessOwner::find($this->created_by);
                if ($processOwner) return [
                    "name" => $processOwner->full_name ?? $processOwner->name ?? "Admin",
                    "bp_code" => "N/A",
                    "user_code" => $processOwner->user_code ?? "N/A",
                    "type" => "Admin"
                ];
            }
        }';

$replace = '            } elseif ($this->creator_type === "admin" || $this->creator_type === "super_admin") {
                $processOwner = \App\Models\ProcessOwner::find($this->created_by);
                if ($processOwner) return [
                    "name" => $processOwner->full_name ?? $processOwner->name ?? "Admin",
                    "bp_code" => "N/A",
                    "user_code" => $processOwner->user_code ?? "N/A",
                    "type" => $processOwner->isSuperAdmin() ? "Super Admin" : "Admin"
                ];
            }
        }';

$c = str_replace(str_replace("\n", "\r\n", $search), str_replace("\n", "\r\n", $replace), $c);
$c = str_replace($search, $replace, $c);

file_put_contents($path, $c);
echo "Fixed PurchaseOrder model creator details.\n";
