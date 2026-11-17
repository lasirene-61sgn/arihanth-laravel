<?php

require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isPhpEnabled', true); // Enables canvas footer script

$dompdf = new Dompdf($options);

function pageHeader($pageNumber, $title, $category = "SYSTEM SPECIFICATION & ARCHITECTURAL STUDY") {
    return '
    <div class="header-table" style="margin-bottom: 6px;">
        <table style="width: 100%; border-bottom: 1.5px solid #1E3A8A; padding-bottom: 3px; margin: 0;">
            <tr>
                <td style="width: 70%; vertical-align: bottom;">
                    <span style="font-size: 6.5pt; font-weight: bold; text-transform: uppercase; color: #D97706; letter-spacing: 0.5px;">' . $category . '</span><br>
                    <span style="font-size: 10pt; font-weight: bold; color: #1E3A8A;">' . $title . '</span>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: bottom;">
                    <span style="font-size: 7.5pt; font-weight: bold; color: #374151;">ARIHANTH JEWELLERS ERP</span><br>
                    <span style="font-size: 6.5pt; color: #6B7280;">SECTION ' . sprintf("%02d", $pageNumber) . ' • 10-PAGE STUDY</span>
                </td>
            </tr>
        </table>
    </div>';
}

$css = '
<style>
    @page {
        margin: 8mm 11mm 11mm 11mm;
    }
    * {
        box-sizing: border-box;
    }
    body {
        font-family: "Helvetica", "Arial", sans-serif;
        font-size: 7.2pt;
        line-height: 1.2;
        color: #1F2937;
        margin: 0;
        padding: 0;
    }
    .page-break {
        page-break-after: always;
    }
    h1, h2, h3, h4 {
        margin: 0 0 2px 0;
        padding: 0;
        color: #111827;
    }
    p {
        margin: 0 0 3px 0;
        text-align: justify;
    }
    
    /* Grid system */
    .row {
        width: 100%;
        margin-bottom: 4px;
    }
    .row:after {
        content: "";
        display: table;
        clear: both;
    }
    .col-6 {
        width: 49%;
        float: left;
    }
    .col-6-right {
        width: 49%;
        float: right;
    }
    .col-4 {
        width: 32%;
        float: left;
        margin-right: 2%;
    }
    .col-4:last-child {
        margin-right: 0;
    }
    .col-3 {
        width: 23.5%;
        float: left;
        margin-right: 2%;
    }
    .col-3:last-child {
        margin-right: 0;
    }

    /* Cards & Containers */
    .card {
        background: #F9FAFB;
        border: 0.8px solid #E5E7EB;
        border-radius: 3px;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    .card-primary {
        background: #F0F4FA;
        border: 0.8px solid #BFDBFE;
        border-left: 3px solid #1E40AF;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    .card-gold {
        background: #FFFBEB;
        border: 0.8px solid #FDE68A;
        border-left: 3px solid #D97706;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    .card-success {
        background: #ECFDF5;
        border: 0.8px solid #A7F3D0;
        border-left: 3px solid #059669;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    .card-danger {
        background: #FEF2F2;
        border: 0.8px solid #FECACA;
        border-left: 3px solid #DC2626;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    
    .card-title {
        font-size: 7.8pt;
        font-weight: bold;
        color: #1E3A8A;
        margin-bottom: 2px;
        display: block;
    }
    
    /* Tables */
    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
        font-size: 6.8pt;
        line-height: 1.15;
    }
    table.data-table th {
        background: #1E3A8A;
        color: #ffffff;
        font-weight: bold;
        text-align: left;
        padding: 2.5px 4px;
        border: 0.5px solid #1E3A8A;
    }
    table.data-table td {
        padding: 2px 4px;
        border: 0.5px solid #E5E7EB;
        vertical-align: top;
    }
    table.data-table tr:nth-child(even) td {
        background: #F9FAFB;
    }
    
    /* Code Blocks */
    .code-box {
        font-family: "Courier New", Courier, monospace;
        font-size: 6.2pt;
        background: #1E293B;
        color: #F8FAFC;
        padding: 3px 5px;
        border-radius: 2px;
        margin-bottom: 3px;
        line-height: 1.15;
    }
    .code-inline {
        font-family: "Courier New", Courier, monospace;
        font-size: 6.5pt;
        background: #E2E8F0;
        color: #0F172A;
        padding: 0.5px 2px;
        border-radius: 2px;
    }

    /* Badges */
    .badge {
        display: inline-block;
        font-size: 5.8pt;
        font-weight: bold;
        padding: 0.5px 3px;
        border-radius: 2px;
        text-transform: uppercase;
    }
    .badge-blue { background: #DBEAFE; color: #1E40AF; }
    .badge-green { background: #D1FAE5; color: #065F46; }
    .badge-amber { background: #FEF3C7; color: #92400E; }
    .badge-red { background: #FEE2E2; color: #991B1B; }
    .badge-purple { background: #EDE9FE; color: #5B21B6; }
    .badge-gray { background: #F3F4F6; color: #374151; }

    /* Flow Steps */
    .step-box {
        border: 0.8px solid #CBD5E1;
        background: #FFFFFF;
        border-radius: 2px;
        padding: 2.5px 4px;
        margin-bottom: 2.5px;
        font-size: 6.8pt;
    }
    .step-num {
        display: inline-block;
        background: #1E3A8A;
        color: #FFFFFF;
        font-weight: bold;
        font-size: 5.8pt;
        width: 11px;
        height: 11px;
        line-height: 11px;
        text-align: center;
        border-radius: 50%;
        margin-right: 3px;
    }

    /* Hero Banner for Page 1 */
    .hero-banner {
        background: #1E3A8A;
        color: #FFFFFF;
        padding: 6px 10px;
        border-radius: 3px;
        margin-bottom: 5px;
    }
    .hero-banner h1 {
        color: #FFFFFF;
        font-size: 12.5pt;
        margin-bottom: 1px;
        font-weight: bold;
        letter-spacing: 0.3px;
    }
    .hero-banner .subtitle {
        color: #FCD34D;
        font-size: 7.8pt;
        font-weight: bold;
        margin-bottom: 2px;
    }
    .hero-banner p {
        color: #E0E7FF;
        font-size: 6.8pt;
        line-height: 1.15;
        margin: 0;
    }
</style>
';

$html = '<!DOCTYPE html><html><head><meta charset="utf-8">' . $css . '</head><body>';

// Canvas footer script for exact page numbering across all pages
$html .= '
<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->getFont("Helvetica", "normal");
    $boldFont = $fontMetrics->getFont("Helvetica", "bold");
    $size = 6.8;
    $color = array(0.35, 0.35, 0.35);
    $gold = array(0.75, 0.45, 0.05);

    // Running Footer (All 10 Pages)
    // A4 width: 595.28, height: 841.89
    $textLeft = "ARIHANTH JEWELLERS ERP • PRODUCTION STUDY: WORK ORDERS, PURCHASE ORDERS & UNIFIED API";
    $textRight = "PAGE " . $PAGE_NUM . " OF " . $PAGE_COUNT;

    $pdf->page_text(31, 824, $textLeft, $font, $size, $color);
    $pdf->page_text(525, 824, $textRight, $boldFont, $size, $gold);
    $pdf->line(31, 820, 564, 820, array(0.82, 0.82, 0.82), 0.5);
}
</script>
';

// =============================================================================
// PAGE 1: EXECUTIVE OVERVIEW, SYSTEM ARCHITECTURE & 7-PANEL ECOSYSTEM
// =============================================================================
$html .= '<div class="page-container">';
$html .= '
<div class="hero-banner">
    <div style="float: right; text-align: right; font-size: 6.5pt; color: #FCD34D;">
        <strong>ENTERPRISE RELEASE 2026</strong><br>
        LARAVEL 12 LTS • MYSQL 8.0<br>
        FINE JEWELRY ERP & CAD SUITE
    </div>
    <h1>ARIHANTH JEWELLERS ERP & MANUFACTURING SUITE</h1>
    <div class="subtitle">Complete Technical Study: Work Orders, Purchase Orders, 7-Panel Portals & Unified API Architecture</div>
    <p>Comprehensive architectural study detailing the database domain models, dual-layer authentication engine, high-precision precious metal specifications, state machines, and RESTful API contracts governing bespoke client orders and bulk stock manufacturing.</p>
</div>

<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Executive Mission & Fine Jewelry Manufacturing Domain</span>
            <p><strong>Arihanth Jewellers ERP</strong> governs high-value fine gold, diamond, and gemstone jewelry production. Unlike generic manufacturing, fine jewelry manufacturing demands precious metal weight accounting down to 0.01g tolerance, strict purity verification (22K 916, 18K 750, 14K 585), rhodium electroplating, stone setting, wax casting, and hallmarking with tight SLA tracking.</p>
        </div>
        <div class="card">
            <span class="card-title">2. Core Technology Stack Architecture</span>
            <table class="data-table">
                <tr><th style="width: 30%;">System Layer</th><th>Technology & Architectural Purpose</th></tr>
                <tr><td><strong>Backend Core</strong></td><td>Laravel 12.0 LTS, PHP 8.2+, Composer 2.x, PSR-4 Autoloading</td></tr>
                <tr><td><strong>Database Engine</strong></td><td>MySQL 8.0 InnoDB, Native JSON Datatypes, Strict FK Constraints</td></tr>
                <tr><td><strong>Auth & Tokens</strong></td><td>Laravel Sanctum 4.3 (Personal Access Tokens), 7 Session Guards</td></tr>
                <tr><td><strong>Real-Time Sync</strong></td><td>Laravel Reverb 1.0 WebSockets, Pusher Protocol, Redis Cache</td></tr>
                <tr><td><strong>Telephony / Video</strong></td><td>Agora RTC Video/Voice Engine via Cyberdeep Token Generator</td></tr>
                <tr><td><strong>Push Engine</strong></td><td>Kreait Laravel Firebase (Google FCM & Apple APNs VoIP)</td></tr>
                <tr><td><strong>Document Engine</strong></td><td>Dompdf 3.1 & FPDF/FPDI (Barcoded job slips, QA vouchers)</td></tr>
            </table>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. End-to-End System Flow: Client to Workshop Pipeline</span>
            <div class="code-box">
[B2B Buyer / Key User / Sales Showroom]
   │ Commission Bespoke Work Order or Submit Bulk PO Stock
   ▼
[Arihanth Admin / Super Admin Control Hub]
   │ Technical Validation, CAD Verification & Purity Checks
   ├──────────────────────────────┬─────────────────────────────┐
   ▼                              ▼                             ▼
[Work Order Pipeline]   [Purchase Order Matrix]       [Raw Inventory]
Single / Bespoke Piece   Multi-Design Stock Batch      Gold / Stones
   │                              │                             │
   ├────── Allocation to Master Craftsman Workshop ─────────────┤
   ▼
[Karigar / Craftsman Floor] ─── (Accept / Reject with Damaged Image)
   │ Metal Casting, Diamond Setting, Soldering, Polishing, Hallmarking
   ▼
[Quality Inspection & Admin Approval] ─── (Undo / Return Flow)
   │ Final Weight Audit, XRF Purity Stamp & Gem Certificate
   ▼
[Order Completion & Dispatch] ─── Real-Time Reverb WebSocket Broadcast
            </div>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">4. The 7 Dedicated System Actors & Panel Portals Overview</span>
    <table class="data-table">
        <tr>
            <th style="width: 14%;">Actor / Panel</th>
            <th style="width: 13%;">Guard & Model</th>
            <th style="width: 17%;">Primary Web Route</th>
            <th style="width: 18%;">Identifier Credential</th>
            <th>Operational Scope & Production Capabilities</th>
        </tr>
        <tr>
            <td><strong>Super Admin</strong></td>
            <td><span class="badge badge-purple">super_admin</span><br>ProcessOwner</td>
            <td><code>/super-admin/dashboard</code></td>
            <td><code>user_code</code> / Email / Mobile</td>
            <td>Full system ownership: user provisioning, freeze accounts, global audits, uncapped OTP returns.</td>
        </tr>
        <tr>
            <td><strong>Admin</strong></td>
            <td><span class="badge badge-blue">admin</span><br>ProcessOwner</td>
            <td><code>/admin/dashboard</code></td>
            <td><code>user_code</code> / Email / Mobile</td>
            <td>Production control: assigns WOs & POs to craftsmen, approves completed items, manages inventory.</td>
        </tr>
        <tr>
            <td><strong>Buyer (B2B)</strong></td>
            <td><span class="badge badge-green">buyer</span><br>Buyer</td>
            <td><code>/buyer/dashboard</code></td>
            <td><code>bp_code</code> / Email / Mobile</td>
            <td>Retail jewelry store owner / B2B partner: places bespoke work orders, tracks production milestones.</td>
        </tr>
        <tr>
            <td><strong>Key User</strong></td>
            <td><span class="badge badge-amber">key_user</span><br>KeyUser</td>
            <td><code>/key-user/dashboard</code></td>
            <td><code>user_code</code> / Email / Mobile</td>
            <td>Buyer enterprise manager: authorized to place orders and manage catalogs on behalf of a Buyer BP code.</td>
        </tr>
        <tr>
            <td><strong>Sales User</strong></td>
            <td><span class="badge badge-gray">web</span><br>User</td>
            <td><code>/user/dashboard</code></td>
            <td><code>user_code</code> / Email / Mobile</td>
            <td>Showroom staff: enters custom client requirements, references designs, tracks counter inquiries.</td>
        </tr>
        <tr>
            <td><strong>Craftsman</strong></td>
            <td><span class="badge badge-amber">craftsman</span><br>Craftman</td>
            <td><code>/craftsman/dashboard</code></td>
            <td><code>craftman_code</code> / Email</td>
            <td>Workshop Master (Karigar): accepts assigned work, manages gold weights, reports damaged items.</td>
        </tr>
        <tr>
            <td><strong>Craftsman Staff</strong></td>
            <td><span class="badge badge-gray">craftsman_staff</span><br>CraftsmanStaff</td>
            <td><code>/craftsman-staff/*</code></td>
            <td><code>staff_code</code> / Mobile</td>
            <td>Workshop floor artisan: assigned specific production sub-tasks (setting, polishing, filing).</td>
        </tr>
    </table>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 2: MULTI-PANEL AUTHENTICATION & SESSION ARCHITECTURE (WEB & API)
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(2, "Multi-Panel Authentication & Session Security Architecture", "CORE AUTHENTICATION ENGINE");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Dual-Layer Web Authentication Architecture</span>
            <p>Arihanth ERP employs an advanced dual-layer authentication topology designed for end-user convenience and enterprise access segmentation:</p>
            <div class="step-box">
                <span class="step-num">A</span><strong>Unified Smart Login (Front Gate):</strong> Hosted at <code>/login</code> and <code>/</code>, managed by <code>UnifiedLoginController</code>. A single form accepts email, mobile, or internal entity code (e.g. <code>BP0012</code>, <code>CR004</code>).
            </div>
            <div class="step-box">
                <span class="step-num">B</span><strong>Dedicated Guard Fallbacks:</strong> Specific panel login URLs (e.g., <code>/admin/login</code>, <code>/super-admin/login</code>, <code>/buyer/login</code>) enforce isolated session guards.
            </div>
        </div>
        <div class="card">
            <span class="card-title">2. Unified Login Resolution Algorithm</span>
            <div class="code-box">
// UnifiedLoginController::login()
$login = trim($request->input(\'login\'));
$pwd   = $request->input(\'password\');
$isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

// 1. ProcessOwner (Admin / Super Admin)
$po = ProcessOwner::where(\'user_code\', $login)
    ->when($isEmail, fn($q)=>$q->orWhere(\'email_id\', $login))->first();
if ($po && Hash::check($pwd, $po->password)) {
    if ($po->is_frozen) return $this->frozenResponse();
    $guard = $po->role === \'super_admin\' ? \'super_admin\' : \'admin\';
    Auth::guard($guard)->login($po);
    $request->session()->regenerate();
    return redirect()->route($guard.\'.dashboard\');
}
// 2. Buyer (bp_code / email) -> guard(\'buyer\')
// 3. Craftsman (craftman_code / email) -> guard(\'craftsman\')
// 4. Key User (user_code / email) -> guard(\'key_user\')
            </div>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. Universal API Auth Architecture (Mobile & REST)</span>
            <p>All mobile clients (iOS, Android) and external B2B integrations authenticate via a single unified endpoint:</p>
            <p><span class="badge badge-blue">POST</span> <code>/api/universal/login</code> or <code>/api/login</code></p>
            <div class="code-box">
{
  "email": "CR0045",            // Email, mobile, or entity code
  "password": "SecretPassword",
  "fcm_token": "fcm_dev_token",  // Auto-synced for push
  "voip_token": "apns_voip_tok", // Synced for iOS VoIP calls
  "device_type": "android"       // "ios" | "android"
}
            </div>
            <p><strong>Response Contract & Token Issuance:</strong></p>
            <div class="code-box">
{
  "success": true,
  "message": "Login successful",
  "role": "craftsman",
  "token": "48|SanctumPlainTextTokenString...",
  "user": {
    "id": 14,
    "craftman_code": "CR0045",
    "name": "Shree Ganesh Workshop",
    "permissions": ["work_order", "global_search", "chat"]
  }
}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">4. Multi-Guard Authentication Matrix & Session Security Standards</span>
    <table class="data-table">
        <tr>
            <th style="width: 13%;">Guard</th>
            <th style="width: 14%;">User Provider</th>
            <th style="width: 15%;">Model Class</th>
            <th style="width: 20%;">Web Login Route</th>
            <th style="width: 18%;">API Token Name</th>
            <th>Security Rules & Middleware</th>
        </tr>
        <tr>
            <td><code>super_admin</code></td>
            <td><code>process_owners</code></td>
            <td><code>App\Models\ProcessOwner</code></td>
            <td><code>/super-admin/login</code></td>
            <td><code>admin_token</code></td>
            <td><code>auth:super_admin</code>, Role checking, Frozen account block</td>
        </tr>
        <tr>
            <td><code>admin</code></td>
            <td><code>process_owners</code></td>
            <td><code>App\Models\ProcessOwner</code></td>
            <td><code>/admin/login</code></td>
            <td><code>admin_token</code></td>
            <td><code>auth:admin</code>, Session fixation defense, IP audit</td>
        </tr>
        <tr>
            <td><code>buyer</code></td>
            <td><code>buyers</code></td>
            <td><code>App\Models\Buyer</code></td>
            <td><code>/buyer/login</code></td>
            <td><code>buyer_token</code></td>
            <td><code>auth:buyer</code>, Scoped strictly to BP code orders</td>
        </tr>
        <tr>
            <td><code>craftsman</code></td>
            <td><code>craftsmen</code></td>
            <td><code>App\Models\Craftman</code></td>
            <td><code>/craftsman/login</code></td>
            <td><code>craftsman_token</code></td>
            <td><code>auth:craftsman</code>, Data masking on proprietary prices</td>
        </tr>
        <tr>
            <td><code>craftsman_staff</code></td>
            <td><code>craftsman_staff</code></td>
            <td><code>App\Models\CraftsmanStaff</code></td>
            <td><code>/craftsman-staff/login</code></td>
            <td><code>craftsman_staff_token</code></td>
            <td><code>auth:craftsman_staff</code>, Inherits workshop BP code</td>
        </tr>
        <tr>
            <td><code>key_user</code></td>
            <td><code>key_users</code></td>
            <td><code>App\Models\KeyUser</code></td>
            <td><code>/key-user/login</code></td>
            <td><code>keyuser_token</code></td>
            <td><code>auth:key_user</code>, Tied to parent Buyer business account</td>
        </tr>
        <tr>
            <td><code>web</code></td>
            <td><code>users</code></td>
            <td><code>App\Models\User</code></td>
            <td><code>/user/login</code></td>
            <td><code>user_token</code></td>
            <td><code>auth:web</code>, General showroom sales staff access</td>
        </tr>
    </table>
</div>

<div class="row" style="margin-bottom: 0;">
    <div class="col-4">
        <div class="card-success">
            <span class="card-title">Push Token Sync</span>
            <p>During authentication, the backend executes <code>syncDeviceTokens()</code>, updating <code>fcm_token</code> and iOS <code>voip_token</code> for instant background alerts on order allocation.</p>
        </div>
    </div>
    <div class="col-4">
        <div class="card-danger">
            <span class="card-title">Freeze Account Policy</span>
            <p>If <code>is_frozen == 1</code>, access is instantly denied across web and API with HTTP 403 / "Your account is frozen. Contact system administrator."</p>
        </div>
    </div>
    <div class="col-4">
        <div class="card-primary">
            <span class="card-title">Session Regeneration</span>
            <p>On every successful web authentication, <code>$request->session()->regenerate()</code> prevents session fixation attacks across all 7 guards.</p>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 3: WORK ORDER (WO) DOMAIN MODEL & SPECIFICATION MATRIX
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(3, "Work Order (WO) System Architecture & Domain Model", "WORK ORDER ENGINE");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Work Order Philosophy & Entity Design</span>
            <p>In fine jewelry manufacturing, a <strong>Work Order (WO)</strong> represents a custom, made-to-order, or bespoke piece commissioned by a Buyer or generated internally from an approved CAD design. The database architecture is encapsulated in the <code>work_orders</code> table, supported by <code>work_order_images</code> for multi-angle photography.</p>
            <p><strong>Auto-Sequencing:</strong> Each Work Order is automatically assigned a unique human-readable tracking identifier upon creation (e.g. <code>WO0001</code>, <code>WO-2026-0042</code>) via an Eloquent model boot hook.</p>
        </div>
        <div class="card">
            <span class="card-title">2. Creator Polymorphic Resolution</span>
            <p>Work orders originate from 5 distinct user tiers. Arihanth ERP solves ID collision across tables via explicit polymorphic tracking:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><code>creator_type</code>: <code>super_admin</code> | <code>admin</code> | <code>buyer</code> | <code>key_user</code> | <code>user</code></li>
                <li><code>created_by</code>: Foreign key ID of the creating record</li>
                <li><code>creator_user_code</code>: Explicit BP code or user code (e.g. <code>BP014</code>, <code>KU002</code>)</li>
            </ul>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. Physical Jewelry Specification Attributes</span>
            <p>Fine jewelry requires an exhaustive matrix of parameters that dictate factory floor manufacturing. The table below lists the operational fields in <code>work_orders</code>:</p>
            <table class="data-table" style="font-size: 6.5pt;">
                <tr><th>Attribute</th><th>Data Type</th><th>Manufacturing Context & Standards</th></tr>
                <tr><td><code>weight_from</code><br><code>weight_to</code></td><td>Decimal(8,2)</td><td>Target gold weight range in grams (e.g. 14.50g to 15.20g). Critical for casting metal budget.</td></tr>
                <tr><td><code>hallmark</code></td><td>Varchar(50)</td><td>Purity certification: 916 (22K Gold), 750 (18K Gold), 585 (14K Gold), or Silver 925.</td></tr>
                <tr><td><code>rodium</code></td><td>Varchar(50)</td><td>Electroplating finish: White Rhodium, Yellow Gold flash, Rose Gold, or Two-Tone combination.</td></tr>
                <tr><td><code>screw_name</code></td><td>Varchar(50)</td><td>Earring/attachment mechanism: Bombay Screw, South Screw, Madras Screw, or Push Press.</td></tr>
                <tr><td><code>hook</code></td><td>Varchar(50)</td><td>Pendant/chain fastening: S-Hook, Round Hook, Lobster Clasp, or Box Clasp.</td></tr>
                <tr><td><code>size</code> / <code>length</code></td><td>Varchar(50)</td><td>Ring size (Indian/US), Bangle circumference (2.4, 2.6, 2.8), Necklace length (16", 18", 22").</td></tr>
                <tr><td><code>stone</code></td><td>Text</td><td>Gemstone specification: Natural Diamonds (VVS-EF), Moissanite, Emerald, Ruby, Polki/Kundan.</td></tr>
                <tr><td><code>enamel</code></td><td>Varchar(100)</td><td>Meenakari enamel work colors (e.g., Red/Green traditional Rajasthani enamel).</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">4. Dual Order Ingestion Modalities: Physical CAD/PDF vs Digital Catalog</span>
    <div class="row" style="margin-bottom: 0;">
        <div class="col-6">
            <div class="step-box">
                <strong>Modality A: CAD Drawing / Customer Slip (PDF Order)</strong><br>
                Used when B2B buyers upload physical handwritten purchase slips or 3D Matrix CAD sheets.
                <ul style="margin: 1px 0 0 0; padding-left: 12px; font-size: 6.5pt;">
                    <li>Stored in <code>public/images/work-orders/</code> or S3.</li>
                    <li>Attribute <code>is_pdf_work_order</code> flags specialized rendering.</li>
                    <li>Sensitive client information is automatically masked before workshop dispatch.</li>
                </ul>
            </div>
        </div>
        <div class="col-6-right">
            <div class="step-box">
                <strong>Modality B: Digital Catalog Commission</strong><br>
                Direct digital creation linking to the master product and design library:
                <ul style="margin: 1px 0 0 0; padding-left: 12px; font-size: 6.5pt;">
                    <li>Direct foreign keys: <code>product_category_id</code>, <code>subcategory_id</code>.</li>
                    <li>Linked attributes: <code>product_code</code>, <code>design_code</code>, <code>product_name</code>.</li>
                    <li>Inherits default standard weights and metal losses from master catalogue.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 3px;">
    <span class="card-title">5. Database Schema Blueprint: <code>work_orders</code> Table Columns</span>
    <table class="data-table" style="font-size: 6.5pt;">
        <tr>
            <th style="width: 22%;">Column Name</th>
            <th style="width: 18%;">SQL Data Type</th>
            <th style="width: 20%;">Default / Key</th>
            <th>Functional & Manufacturing Purpose</th>
        </tr>
        <tr><td><code>id</code></td><td>BIGINT UNSIGNED</td><td>PRIMARY KEY AUTO</td><td>Unique internal primary key sequence.</td></tr>
        <tr><td><code>work_order_number</code></td><td>VARCHAR(191)</td><td>UNIQUE NOT NULL</td><td>Public tracking identifier (e.g. <code>WO-2026-0042</code>).</td></tr>
        <tr><td><code>bp_code</code></td><td>VARCHAR(191)</td><td>INDEX NOT NULL</td><td>Buyer Business Partner Code (client owner).</td></tr>
        <tr><td><code>due_date</code>, <code>craftsman_due_date</code></td><td>DATE</td><td>NULLABLE</td><td>Promised delivery date and workshop target completion date.</td></tr>
        <tr><td><code>weight_from</code>, <code>weight_to</code></td><td>DECIMAL(8,2)</td><td>NULLABLE</td><td>Gold mass budget range in grams (0.01g tolerance).</td></tr>
        <tr><td><code>allocated_craftsman_bp_code</code></td><td>VARCHAR(191)</td><td>INDEX NULLABLE</td><td>Workshop Karigar assigned to execute the order.</td></tr>
        <tr><td><code>status</code>, <code>craftsman_status</code></td><td>VARCHAR(50)</td><td>DEFAULT \'created\'</td><td>Master state (created, allocated, in_process, for_approval, completed).</td></tr>
        <tr><td><code>rejection_reason</code>, <code>damaged_image</code></td><td>TEXT / VARCHAR</td><td>NULLABLE</td><td>Workshop defect documentation if rejected.</td></tr>
        <tr><td><code>admin_undo_count</code>, <code>return_due_date</code></td><td>INT / DATE</td><td>DEFAULT 0</td><td>Audit metrics for state reversions and rework scheduling.</td></tr>
    </table>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 4: WORK ORDER STATE MACHINE, PRODUCTION LIFECYCLE & COLOR CODING
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(4, "Work Order State Machine & Color Coding Engine", "LIFECYCLE & SLA TRACKING");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Dual-Status Synchronization Model</span>
            <p>Work Order progress is governed by two synchronized status fields representing administrative oversight versus workshop execution:</p>
            <table class="data-table">
                <tr><th>Stage</th><th>Admin Status (<code>status</code>)</th><th>Craftsman Status (<code>craftsman_status</code>)</th></tr>
                <tr><td>1. Created</td><td><span class="badge badge-gray">created</span></td><td><em>NULL</em> (Unassigned)</td></tr>
                <tr><td>2. Allocated</td><td><span class="badge badge-blue">allocated</span></td><td><span class="badge badge-blue">allocated</span></td></tr>
                <tr><td>3. In-Process</td><td><span class="badge badge-amber">in_process</span></td><td><span class="badge badge-amber">in_process</span></td></tr>
                <tr><td>4. For Approval</td><td><span class="badge badge-purple">for_approval</span></td><td><span class="badge badge-green">completed</span></td></tr>
                <tr><td>5. Completed</td><td><span class="badge badge-green">completed</span></td><td><span class="badge badge-green">completed</span></td></tr>
                <tr><td>6. Rejected</td><td><span class="badge badge-red">created / unallocated</span></td><td><span class="badge badge-red">rejected</span></td></tr>
            </table>
        </div>
        <div class="card-gold">
            <span class="card-title">2. Step-by-Step Production Lifecycle</span>
            <div class="step-box">
                <span class="step-num">1</span><strong>Order Placement:</strong> Buyer submits order with metal specs and design sketch. Status initialized to <code>created</code>.
            </div>
            <div class="step-box">
                <span class="step-num">2</span><strong>Workshop Allocation:</strong> Admin assigns Karigar code & sets <code>craftsman_due_date</code>. Both statuses move to <code>allocated</code>.
            </div>
            <div class="step-box">
                <span class="step-num">3</span><strong>Craftsman Acceptance:</strong> Karigar reviews CAD & casting feasibility. Clicks "Accept" (status -> <code>in_process</code>).
            </div>
            <div class="step-box">
                <span class="step-num">4</span><strong>Floor Production:</strong> Wax carving, casting, diamond setting, filing, polishing, and hallmark stamping.
            </div>
            <div class="step-box">
                <span class="step-num">5</span><strong>Inspection & Signoff:</strong> Karigar submits piece. Admin checks gross weight against tolerance, tests purity, approves order.
            </div>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card">
            <span class="card-title">3. Dynamic 12-Hour SLA & UI Color-Coding Engine</span>
            <p>To eliminate production bottlenecks, the API dynamically injects real-time color telemetry calculated on every request via <code>transformWorkOrderResponse()</code>:</p>
            <table class="data-table">
                <tr><th>Visual Indicator</th><th>Hex Code</th><th>Triggering Business Condition</th></tr>
                <tr>
                    <td><span class="badge badge-red">LIGHT RED</span></td>
                    <td><code>#FFCDD2</code></td>
                    <td><strong>Overdue:</strong> Due date has passed (<code>due_date &lt; now()</code>) and order is not yet completed. High priority alert.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-amber">LIGHT ORANGE</span></td>
                    <td><code>#FFE0B2</code></td>
                    <td><strong>In-Process:</strong> Accepted by Karigar (<code>craftsman_status == \'in_process\'</code>). Active workshop production.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-blue">LIGHT BLUE</span></td>
                    <td><code>#E3F2FD</code></td>
                    <td><strong>Allocated (&le;12 Hours):</strong> Freshly assigned to Karigar. Within standard response grace period.</td>
                </tr>
                <tr>
                    <td><span class="badge" style="background: #FFF9C4; color: #854D0E;">LIGHT YELLOW</span></td>
                    <td><code>#FFF9C4</code></td>
                    <td><strong>Allocated (&gt;12 Hours):</strong> Karigar has not accepted within 12 hours. Escalation warning flag for workshop supervisor.</td>
                </tr>
            </table>
            <div class="code-box">
// API Color Coding Implementation Snippet
if ($workOrder->isOverdue()) {
    $colorKey = \'light-red\';    $colorHex = \'#FFCDD2\';
} elseif ($workOrder->craftsman_status === \'in_process\') {
    $colorKey = \'light-orange\'; $colorHex = \'#FFE0B2\';
} elseif ($workOrder->status === \'allocated\') {
    $hours = now()->diffInHours($workOrder->updated_at);
    $colorKey = $hours &lt;= 12 ? \'light-blue\' : \'light-yellow\';
    $colorHex = $hours &lt;= 12 ? \'#E3F2FD\'    : \'#FFF9C4\';
}
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2px;">
    <span class="card-title">4. Exception Protocols: Rejection, Undo & Return Handlers</span>
    <div class="row" style="margin-bottom: 0;">
        <div class="col-4">
            <div class="card-danger">
                <strong>Craftsman Rejection Flow</strong>
                <p>If gold weight is insufficient or CAD is defective, Karigar triggers <code>rejectWorkOrder()</code>. Requires mandatory <code>rejection_reason</code> and optional <code>damaged_image</code> upload. Order unallocates and reverts to Admin queue.</p>
            </div>
        </div>
        <div class="col-4">
            <div class="card-primary">
                <strong>Admin / SuperAdmin Undo</strong>
                <p>If an order was mistakenly allocated or marked completed, Admin can revert state. Tracked via <code>admin_undo_count</code> and <code>superadmin_undo_count</code>. Protected by OTP verification in production mode.</p>
            </div>
        </div>
        <div class="col-4">
            <div class="card-gold">
                <strong>Return for Rework Protocol</strong>
                <p>If quality inspection detects porosity, loose stones, or polish defects, Admin issues a Return. Requires <code>return_due_date</code> and <code>return_note</code>. Recorded in <code>admin_return_count</code> for Karigar scoring.</p>
            </div>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 5: WORK ORDER WEB PORTALS (ADMIN, CRAFTSMAN & BUYER)
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(5, "Work Order Web Portals Implementation & Workflows", "WEB INTERFACES");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Admin & SuperAdmin Control Center</span>
            <p>The administrative suite (under prefix <code>/admin/work-order</code>) provides high-throughput production orchestration across 8 interactive tabs:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><strong>New Orders:</strong> Unassigned bespoke commissions awaiting workshop allocation.</li>
                <li><strong>Allocated Orders:</strong> Dispatched to Karigars, tracking 12-hour acceptance SLA.</li>
                <li><strong>In-Process Orders:</strong> Actively being crafted on the factory floor.</li>
                <li><strong>For Approval Orders:</strong> Submitted by Karigars, ready for physical QC inspection.</li>
                <li><strong>Completed Orders:</strong> Quality approved, stamped, and ready for dispatch.</li>
                <li><strong>Rejected Orders:</strong> Declined by craftsmen with defect documentation.</li>
                <li><strong>Overdue Orders:</strong> Breached promised delivery dates requiring urgent intervention.</li>
                <li><strong>All Orders:</strong> Master search grid with multi-parameter filtering.</li>
            </ul>
        </div>
        <div class="card">
            <span class="card-title">2. Single & Bulk Allocation Forms</span>
            <p>Admins can allocate orders individually via <code>/{workOrder}/allocate</code> or execute batch allocations via <code>/bulk-allocate</code>:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li>Select multiple orders using tabular checkboxes.</li>
                <li>Select target Craftsman from active workshop registry.</li>
                <li>Set synchronized <code>craftsman_due_date</code>.</li>
                <li>System triggers background FCM/APNs push alert to the Karigar mobile app.</li>
            </ul>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. Craftsman & Staff Workbench</span>
            <p>Located at <code>/craftsman/work-order</code> and <code>/craftsman-staff/work-order</code>:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><strong>Scoped Workspace:</strong> Karigar sees only orders matching their <code>allocated_craftsman_bp_code</code>.</li>
                <li><strong>Data Masking Engine:</strong> Proprietary client pricing, wholesale rates, and customer names are masked (<code>***</code>) to safeguard commercial secrecy.</li>
                <li><strong>One-Click Actions:</strong> Instant buttons for "Accept Order", "Reject Order" (opens camera/file modal for damaged casting upload), and "Mark Completed".</li>
                <li><strong>Sub-Staff Delegation:</strong> Workshop master can re-delegate orders to internal bench jewelers (<code>craftsman_staff_id</code>).</li>
            </ul>
        </div>
        <div class="card-success">
            <span class="card-title">4. Buyer & Key User Client Portal</span>
            <p>Located at <code>/buyer/work-order</code> and <code>/key-user/work-order</code>:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li>B2B clients create bespoke orders with custom gold weights, sizes, and reference CAD drawings.</li>
                <li>Live milestone progression bar (Received -> Processing -> Ready).</li>
                <li>Internal workshop allocations and Karigar identities remain completely invisible.</li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">5. Master Work Order Web Routes Blueprint (Controllers & Middlewares)</span>
    <table class="data-table">
        <tr>
            <th style="width: 20%;">HTTP Method & URL</th>
            <th style="width: 24%;">Controller Action</th>
            <th style="width: 17%;">Route Name</th>
            <th style="width: 13%;">Auth Guard</th>
            <th>Operational Purpose</th>
        </tr>
        <tr>
            <td><code>GET /admin/work-order</code></td>
            <td><code>AdminWorkOrderController@index</code></td>
            <td><code>admin.work-order.index</code></td>
            <td><code>admin, super_admin</code></td>
            <td>Master tabbed work order listing with real-time AJAX stats.</td>
        </tr>
        <tr>
            <td><code>GET /admin/work-order/create</code></td>
            <td><code>AdminWorkOrderController@create</code></td>
            <td><code>admin.work-order.create</code></td>
            <td><code>admin, super_admin</code></td>
            <td>Bespoke order creation form with dynamic category cascaders.</td>
        </tr>
        <tr>
            <td><code>POST /admin/work-order/bulk-allocate</code></td>
            <td><code>AdminWorkOrderController@bulkAllocate</code></td>
            <td><code>admin.work-order.bulk-allocate</code></td>
            <td><code>admin, super_admin</code></td>
            <td>Batch assignment of selected orders to a specific craftsman.</td>
        </tr>
        <tr>
            <td><code>POST /admin/work-order/bulk-approve</code></td>
            <td><code>AdminWorkOrderController@bulkApprove</code></td>
            <td><code>admin.work-order.bulk-approve</code></td>
            <td><code>admin, super_admin</code></td>
            <td>Bulk final sign-off for completed manufacturing batches.</td>
        </tr>
        <tr>
            <td><code>POST /admin/work-order/{id}/undo</code></td>
            <td><code>AdminWorkOrderController@undo</code></td>
            <td><code>admin.work-order.undo</code></td>
            <td><code>admin, super_admin</code></td>
            <td>Reverts an accidental completion or allocation with OTP check.</td>
        </tr>
        <tr>
            <td><code>GET /craftsman/work-order</code></td>
            <td><code>CraftsmanWorkOrderController@index</code></td>
            <td><code>craftsman.work-order.index</code></td>
            <td><code>craftsman</code></td>
            <td>Workshop orders filtered strictly by authenticated craftsman.</td>
        </tr>
        <tr>
            <td><code>POST /craftsman/work-order/{id}/accept</code></td>
            <td><code>CraftsmanWorkOrderController@accept</code></td>
            <td><code>craftsman.work-order.accept</code></td>
            <td><code>craftsman</code></td>
            <td>Karigar signals acceptance; transitions order into active production.</td>
        </tr>
        <tr>
            <td><code>POST /craftsman/work-order/{id}/reject</code></td>
            <td><code>CraftsmanWorkOrderController@reject</code></td>
            <td><code>craftsman.work-order.reject</code></td>
            <td><code>craftsman</code></td>
            <td>Karigar declines piece with defect reason and photographic evidence.</td>
        </tr>
        <tr>
            <td><code>GET /buyer/work-order</code></td>
            <td><code>BuyerWorkOrderController@index</code></td>
            <td><code>buyer.work-order.index</code></td>
            <td><code>buyer</code></td>
            <td>B2B client view of commissioned bespoke pieces.</td>
        </tr>
    </table>
</div>

<div class="row" style="margin-bottom: 0;">
    <div class="col-6">
        <div class="card">
            <span class="card-title">Production Job Slips & Barcodes</span>
            <p>Every Work Order generates a physical Factory Job Slip via Dompdf/FPDI (<code>/{id}/print</code>) with 2D DataMatrix/QR codes for factory floor scanners.</p>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card">
            <span class="card-title">Bulk Excel Import & Export Suite</span>
            <p>Powered by Maatwebsite Excel. Ingests via <code>WorkOrderImport</code> and <code>OrderListImport</code>. Exports filtered sheets via <code>AdminWorkOrderExport</code>.</p>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 6: WORK ORDER RESTFUL API SUITE
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(6, "Work Order RESTful API Suite & Integration Contracts", "RESTful API CONTRACTS");

$html .= '
<div class="card-primary">
    <span class="card-title">1. Unified Architecture: Single API Suite for All Panels</span>
    <p>Arihanth ERP routes all mobile clients (iOS / Android), tablet shopfloor terminals, and external web clients through a single unified endpoint suite: <code>/api/common/work-orders/*</code>. The underlying <code>API\Common\WorkOrderController</code> dynamically inspects the authenticated Sanctum user role, scopes SQL queries, and applies role-based data masking.</p>
</div>

<div class="row">
    <div class="col-6">
        <div class="card">
            <span class="card-title">2. Core Query & Mutation Endpoints</span>
            <table class="data-table">
                <tr><th>HTTP & Endpoint</th><th>Scope & Purpose</th></tr>
                <tr><td><code>GET /api/common/work-orders</code></td><td>Lists orders with tabs, counts, color telemetry & role scoping.</td></tr>
                <tr><td><code>POST /api/common/work-orders</code></td><td>Creates new bespoke WO (multipart with CAD drawings).</td></tr>
                <tr><td><code>GET /api/common/work-orders/{id}</code></td><td>Detailed WO payload, image gallery & creator metadata.</td></tr>
                <tr><td><code>POST /api/common/work-orders/{id}</code></td><td>Updates specifications (Admin or Buyer draft).</td></tr>
                <tr><td><code>DELETE /api/common/work-orders/{id}</code></td><td>Soft deletes unallocated draft work order.</td></tr>
            </table>
        </div>
        <div class="card-gold">
            <span class="card-title">3. Workflow Transition Mutations</span>
            <table class="data-table">
                <tr><th>HTTP & Endpoint</th><th>Role</th><th>Action</th></tr>
                <tr><td><code>POST .../{id}/allocate</code></td><td>Admin</td><td>Assigns to <code>craftsman_code</code>.</td></tr>
                <tr><td><code>POST .../bulk-allocate</code></td><td>Admin</td><td>Batch assigns multiple WOs.</td></tr>
                <tr><td><code>POST .../{id}/accept</code></td><td>Craftsman</td><td>Accepts job (moves to in-process).</td></tr>
                <tr><td><code>POST .../{id}/reject</code></td><td>Craftsman</td><td>Rejects with <code>rejection_reason</code>.</td></tr>
                <tr><td><code>POST .../{id}/complete</code></td><td>Craftsman</td><td>Submits for final QA approval.</td></tr>
                <tr><td><code>POST .../bulk-approve</code></td><td>Admin</td><td>Batch approves completed WOs.</td></tr>
            </table>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card">
            <span class="card-title">4. Sample Request: Create Work Order</span>
            <div class="code-box">
POST /api/common/work-orders
Content-Type: multipart/form-data
Authorization: Bearer 48|SanctumToken...

bp_code=BP0012
customer_name=Rajesh+Jewellers
reference_no=REF-2026-99
due_date=2026-12-15
product_category_id=4
subcategory_id=12
design_code=DES-7741
weight_from=14.50
weight_to=15.20
hallmark=916
rodium=Two-Tone
screw_name=Bombay+Screw
size=2.6
quantity=1
priority=high
product_image=@cad_design_render.jpg
gallery_images[]=@side_profile.jpg
            </div>
        </div>
        <div class="card-success">
            <span class="card-title">5. Sample Response (With Color Telemetry)</span>
            <div class="code-box">
{
  "success": true,
  "data": {
    "id": 1084,
    "work_order_number": "WO-2026-1084",
    "bp_code": "BP0012",
    "status": "in_process",
    "craftsman_status": "in_process",
    "color_key": "light-orange",
    "color_hex": "#FFE0B2",
    "is_pdf_work_order": false,
    "weight_from": "14.50",
    "weight_to": "15.20",
    "hallmark": "916",
    "gallery_images": [
      "https://erp.arihanth.com/storage/wo/1084_front.jpg"
    ],
    "creator_details": {
      "name": "Sunil Mehta",
      "type": "Key User",
      "bp_code": "BP0012"
    }
  }
}
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2px;">
    <span class="card-title">6. API Helper Endpoints & Proprietary Data Masking Specification</span>
    <div class="row" style="margin-bottom: 0;">
        <div class="col-4">
            <div class="step-box">
                <strong>Business Partner Codes</strong><br>
                <code>GET /api/common/work-orders/helpers/bp-codes</code><br>
                Returns active verified buyer BP codes for auto-completion in order entry forms.
            </div>
        </div>
        <div class="col-4">
            <div class="step-box">
                <strong>Craftsman Codes</strong><br>
                <code>GET /api/common/work-orders/helpers/craftman-codes</code><br>
                Returns workshop master IDs and capacity indicators for allocation dialogs.
            </div>
        </div>
        <div class="col-4">
            <div class="step-box">
                <strong>On-Demand PDF Generation</strong><br>
                <code>GET /api/common/work-orders/generate-pdf/{id?}</code><br>
                Generates signed, printable production job slips on mobile devices on demand.
            </div>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 7: PURCHASE ORDER (PO) ARCHITECTURE & MULTI-ITEM MATRIX
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(7, "Purchase Order (PO) Architecture & Multi-Item Product Matrix", "PURCHASE ORDER ENGINE");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Purchase Order Conceptual Philosophy</span>
            <p>While Work Orders govern bespoke commissions, <strong>Purchase Orders (POs)</strong> are the backbone of Arihanth Jewellers wholesale stock production and raw inventory replenishment. A single Purchase Order represents a high-volume manufacturing contract issued to a master workshop, grouping tens or hundreds of distinct jewelry designs under one voucher.</p>
            <p><strong>Code Auto-Sequencing:</strong> Governed by <code>PurchaseOrder::generatePurchaseOrderCode()</code>. Automatically increments sequential procurement numbers formatted as <code>PA0001</code>, <code>PA0002</code>, through <code>PA9999</code>.</p>
        </div>
        <div class="card">
            <span class="card-title">2. Database Schema Blueprint: <code>purchase_orders</code></span>
            <div class="code-box">
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_code` varchar(50) NOT NULL UNIQUE,  -- e.g. PA0142
  `due_date` date DEFAULT NULL,
  `craftsman_due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `items` json NOT NULL,                             -- Array of line items
  `rejected_items` json DEFAULT NULL,                -- Rejected items history
  `status` varchar(50) NOT NULL DEFAULT \'created\',   -- created|allocated|in_process...
  `allocated_craftsman_code` varchar(50) DEFAULT NULL,
  `craftsman_status` varchar(50) DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `creator_type` varchar(50) DEFAULT \'admin\',
  `allocated_by` bigint unsigned DEFAULT NULL,
  `allocated_at` timestamp DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `craftsman_staff_id` bigint unsigned DEFAULT NULL,
  `accepted_by_staff_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
            </div>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. The Dynamic Multi-Item JSON Architecture</span>
            <p>Arihanth ERP utilizes a highly optimized MySQL 8.0 JSON array structure in the <code>items</code> column. This eliminates costly multi-table joins while permitting complex, nested multi-gram weight tiers per jewelry design:</p>
            <div class="code-box">
[
  {
    "product_id": 142,
    "category": "Bangles",
    "subcategory": "Kada Antique",
    "item_notes": "Matt finish with antique polish",
    "item_size": "2.6",
    "grams": ["22.50", "25.00", "28.00"],   // Weight tiers
    "quantity": [10, 20, 5],                 // Qty per tier
    "total": "865.00",                       // Calculated weight
    "image": "images/purchase-orders/pa142_item0.jpg"
  },
  {
    "product_id": 89,
    "category": "Rings",
    "subcategory": "Men Solitaire",
    "item_notes": "VVS Diamonds, 18K Yellow Gold",
    "item_size": "22",
    "grams": ["8.50", "9.20"],
    "quantity": [15, 15],
    "total": "265.50",
    "image": "images/purchase-orders/pa142_item1.jpg"
  }
]
            </div>
            <p><strong>Total Calculated Weight:</strong> Aggregated as: &sum; (grams[i] &times; quantity[i]) across all item tiers to establish the exact gold mass allocation for the workshop.</p>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">4. Architectural Comparison: Work Order (WO) vs Purchase Order (PO)</span>
    <table class="data-table">
        <tr>
            <th style="width: 20%;">Architectural Dimension</th>
            <th style="width: 40%;">Work Order (WO)</th>
            <th style="width: 40%;">Purchase Order (PO)</th>
        </tr>
        <tr>
            <td><strong>Primary Business Purpose</strong></td>
            <td>Bespoke, custom client order or individual design commission.</td>
            <td>Bulk showroom stock replenishment or batch wholesale production.</td>
        </tr>
        <tr>
            <td><strong>Client Visibility</strong></td>
            <td>Visible to B2B Buyers, Key Users, and Showroom Sales Staff.</td>
            <td>Internal manufacturing document (Admin & Craftsman only).</td>
        </tr>
        <tr>
            <td><strong>Item Topology</strong></td>
            <td>Single distinct jewelry item with singular weight bounds.</td>
            <td>Multi-item array grouping multiple designs and weight grades.</td>
        </tr>
        <tr>
            <td><strong>Data Structure</strong></td>
            <td>Relational table columns with dedicated image relationships.</td>
            <td>Embedded JSON document architecture (<code>items</code>, <code>rejected_items</code>).</td>
        </tr>
        <tr>
            <td><strong>Code Numbering</strong></td>
            <td><code>WO0001</code>, <code>WO-2026-0042</code></td>
            <td><code>PA0001</code> through <code>PA9999</code></td>
        </tr>
        <tr>
            <td><strong>Fulfillment Modality</strong></td>
            <td>Single unit completion or full rejection.</td>
            <td>Granular item-by-item processing and partial lot acceptance.</td>
        </tr>
    </table>
</div>

<div class="row" style="margin-bottom: 0;">
    <div class="col-6">
        <div class="card-success">
            <span class="card-title">Per-Item Visual CAD Storage</span>
            <p>Every item within the JSON array supports an independent reference image stored in <code>public/images/purchase-orders/</code>. Appended as absolute URLs via <code>items_with_image_urls</code>.</p>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-danger">
            <span class="card-title">Rejected Items Audit History</span>
            <p>Items rejected by Karigars during intake are segregated into the <code>rejected_items</code> JSON column with timestamps and rejection notes, preserving a pristine audit trail.</p>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 8: PURCHASE ORDER LIFECYCLE & PARTIAL PROCESSING ENGINE
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(8, "Purchase Order Lifecycle, Allocation & Partial Fulfillment", "PO LIFECYCLE & WORKSHOP GATING");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Multi-Tier Status State Machine</span>
            <p>Purchase Orders progress through rigorous stage gates designed for multi-item manufacturing batches:</p>
            <div class="step-box">
                <span class="step-num">1</span><strong>Created:</strong> Initial state upon submission. Status is <code>created</code>, Karigar unassigned.
            </div>
            <div class="step-box">
                <span class="step-num">2</span><strong>Allocated:</strong> Admin assigns to workshop. Status moves to <code>allocated</code>, triggering push notifications.
            </div>
            <div class="step-box">
                <span class="step-num">3</span><strong>In-Process:</strong> Karigar accepts the PO package. Floor production begins across all line items.
            </div>
            <div class="step-box">
                <span class="step-num">4</span><strong>For Approval:</strong> Karigar completes all items and submits the batch for administrative quality sign-off.
            </div>
            <div class="step-box">
                <span class="step-num">5</span><strong>Completed:</strong> Admin conducts physical metal purity tests, verifies weight totals, and approves the voucher.
            </div>
        </div>
        <div class="card">
            <span class="card-title">2. Allocation & Reallocation Protocols</span>
            <p>PO allocation supports both single assignment and bulk operations:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><code>allocateStore()</code>: Binds <code>allocated_craftsman_code</code>, records <code>allocated_by</code>, <code>allocated_at</code>, and sets <code>craftsman_due_date</code>.</li>
                <li><code>bulkAllocate()</code>: Enables an administrator to select 10+ PO vouchers simultaneously and assign them to an authorized workshop in a single transaction.</li>
                <li><code>reallocate()</code>: Transfers unstarted or delayed orders to an alternate workshop with full audit tracking.</li>
            </ul>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. Item-Level Partial Processing Protocol</span>
            <p>In wholesale jewelry production, a single PO may contain 50 bangles and 100 rings. Bangles may finish casting 5 days ahead of rings. Arihanth ERP features specialized partial processing endpoints:</p>
            <table class="data-table">
                <tr><th>Endpoint Action</th><th>Handler</th><th>Operational Impact</th></tr>
                <tr>
                    <td><code>process-items</code></td>
                    <td><code>processItems()</code></td>
                    <td>Craftsman updates partial progress on specific line items (e.g. Casting Done, In Setting).</td>
                </tr>
                <tr>
                    <td><code>complete-items</code></td>
                    <td><code>completeItems()</code></td>
                    <td>Craftsman marks specific item indices as finished. Items are cleared for hallmark inspection.</td>
                </tr>
                <tr>
                    <td><code>items/{index}/accept</code></td>
                    <td><code>acceptItem()</code></td>
                    <td>Workshop formally accepts item index; locks CAD design into production.</td>
                </tr>
                <tr>
                    <td><code>items/{index}/reject</code></td>
                    <td><code>rejectItem()</code></td>
                    <td>Workshop rejects specific item index. Item is moved from <code>items</code> to <code>rejected_items</code> JSON array.</td>
                </tr>
            </table>
            <div class="code-box">
// Line-Item Segregation Algorithm
$items = $po->items;
$rejectedItem = $items[$index];
$rejectedItem[\'rejected_at\'] = now()->toISOString();
$rejectedItem[\'rejection_reason\'] = $request->reason;

unset($items[$index]);
$po->items = array_values($items);
$po->rejected_items = array_merge($po->rejected_items ?? [], [$rejectedItem]);
$po->save();
            </div>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">4. Purchase Order Quality Verification & Admin Clearance Pipeline</span>
    <table class="data-table">
        <tr>
            <th style="width: 18%;">Inspection Checkpoint</th>
            <th style="width: 32%;">Physical & Metallurgical Verification</th>
            <th style="width: 25%;">System Attribute Update</th>
            <th>Resolution on Failure</th>
        </tr>
        <tr>
            <td><strong>1. Metal Weight Audit</strong></td>
            <td>Batch gross weight weighed on calibrated digital scale against &sum;(grams &times; qty).</td>
            <td>Reconciled in <code>notes</code> / item weight logs.</td>
            <td>Metal shortage billed back to Karigar gold ledger.</td>
        </tr>
        <tr>
            <td><strong>2. Purity & Hallmark</strong></td>
            <td>XRF spectrometer purity scan confirming 22K (91.6%) or 18K (75.0%) gold standard.</td>
            <td>Logged in Bureau of Indian Standards (BIS) registry.</td>
            <td>Lot rejected; sent for chemical refining.</td>
        </tr>
        <tr>
            <td><strong>3. Stone Setting QA</strong></td>
            <td>Microscope check for prong tightness, stone alignment, and surface polish finish.</td>
            <td>Updated via <code>completeItems()</code>.</td>
            <td>Returned for workshop bench polishing.</td>
        </tr>
        <tr>
            <td><strong>4. Final Signoff</strong></td>
            <td>Admin executes <code>approve()</code> or <code>bulkApprove()</code> to close procurement contract.</td>
            <td>Status -> <code>completed</code>, <code>approved_by</code> set.</td>
            <td>Items transferred to active showroom inventory.</td>
        </tr>
    </table>
</div>

<div class="row" style="margin-bottom: 0;">
    <div class="col-6">
        <div class="card">
            <span class="card-title">Voucher PDF Generation</span>
            <p>Every Purchase Order compiles into an official factory procurement voucher via Dompdf (<code>/{purchaseOrder}/print</code>) with item breakdown tables, Karigar signatures, and delivery terms.</p>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card">
            <span class="card-title">Real-Time Allocation Notifications</span>
            <p>Upon execution of <code>allocate()</code>, the event triggers <code>PurchaseOrderAllocated</code> notification, transmitting push alerts via Firebase and updating the workshop tablet terminal via Reverb.</p>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 9: PURCHASE ORDER WEB PORTALS & RESTFUL API ARCHITECTURE
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(9, "Purchase Order Web Portals & RESTful API Architecture", "PO INTERFACES & API SUITE");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Admin Web Portal Implementation</span>
            <p>Located under route group <code>/admin/purchase-order</code>:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><strong>Interactive Creation Wizard:</strong> AJAX cascade lookups for product categories (<code>/get-products-by-category</code>), designs (<code>/get-designs-by-product</code>), and codes (<code>/get-product-by-design-code</code>).</li>
                <li><strong>Dynamic Multi-Row Matrix:</strong> Web UI allows adding unlimited item rows, with dynamic weight tier rows and individual file upload fields.</li>
                <li><strong>Tabbed Management:</strong> <code>created</code>, <code>allocated</code>, <code>in_process</code>, <code>for_approval</code>, <code>completed</code>.</li>
            </ul>
        </div>
        <div class="card">
            <span class="card-title">2. Craftsman PO Web Workspace</span>
            <p>Located under route group <code>/craftsman/purchase-order</code>:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li>Protected by middleware <code>craftsman.permission:purchase_order</code>.</li>
                <li>Workshop sees assigned vouchers, item technical drawings, and gram allocations.</li>
                <li>Form triggers for <code>bulk-accept</code>, <code>bulk-reject</code>, and <code>process-items</code>.</li>
            </ul>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">3. High-Performance MySQL JSON Querying</span>
            <p>Because items are stored as JSON arrays, standard SQL filters fail. Arihanth ERP utilizes advanced MySQL 8.0 JSON path functions:</p>
            <div class="code-box">
// Querying nested category inside items JSON array
$catName = ProductCategory::find($id)->name;
$query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, \'$[*].category\'), ?)", 
    [json_encode($catName)]);

// Searching across items array strings
$query->whereRaw("JSON_SEARCH(items, \'one\', ?) IS NOT NULL", 
    ["%$search%"]);
            </div>
            <p>This delivers sub-millisecond execution speeds even with tens of thousands of historical purchase vouchers.</p>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">4. Purchase Order Common RESTful API Reference (<code>/api/common/purchase-orders/*</code>)</span>
    <table class="data-table">
        <tr>
            <th style="width: 20%;">HTTP Method & Endpoint</th>
            <th style="width: 25%;">Request Parameters / Payload</th>
            <th style="width: 14%;">Allowed Roles</th>
            <th>API Contract & Response Behavior</th>
        </tr>
        <tr>
            <td><code>GET /purchase-orders</code></td>
            <td><code>tab, search, craftsman_code, category_id, sort_by</code></td>
            <td>Admin, Craftsman</td>
            <td>Returns paginated PO list, tab counts, and role-scoped orders.</td>
        </tr>
        <tr>
            <td><code>POST /purchase-orders</code></td>
            <td><code>due_date, notes, items[][category, grams[], quantity[]]</code></td>
            <td>Admin, SuperAdmin</td>
            <td>Creates new PO voucher with auto-generated <code>PAxxxx</code> code.</td>
        </tr>
        <tr>
            <td><code>GET /purchase-orders/{id}</code></td>
            <td><em>None</em> (Path parameter ID)</td>
            <td>Admin, Craftsman</td>
            <td>Returns full PO detail with appended image URLs and item totals.</td>
        </tr>
        <tr>
            <td><code>POST /purchase-orders/{id}/allocate</code></td>
            <td><code>craftsman_code, craftsman_due_date</code></td>
            <td>Admin, SuperAdmin</td>
            <td>Binds workshop Karigar; fires Push notifications.</td>
        </tr>
        <tr>
            <td><code>POST /purchase-orders/bulk-allocate</code></td>
            <td><code>ids[], craftsman_code, craftsman_due_date</code></td>
            <td>Admin, SuperAdmin</td>
            <td>Batch allocates multiple purchase vouchers to one workshop.</td>
        </tr>
        <tr>
            <td><code>POST /purchase-orders/{id}/approve</code></td>
            <td><em>None</em></td>
            <td>Admin, SuperAdmin</td>
            <td>Final administrative sign-off; moves status to <code>completed</code>.</td>
        </tr>
        <tr>
            <td><code>POST /purchase-orders/{id}/complete-items</code></td>
            <td><code>completed_indices[]</code></td>
            <td>Admin, Craftsman</td>
            <td>Marks selected item indices within the JSON array as finished.</td>
        </tr>
        <tr>
            <td><code>POST .../{id}/items/{idx}/reject</code></td>
            <td><code>reason, damaged_image</code></td>
            <td>Craftsman</td>
            <td>Moves specific item from <code>items</code> to <code>rejected_items</code>.</td>
        </tr>
        <tr>
            <td><code>GET /purchase-orders/generate-pdf/{id?}</code></td>
            <td><em>None</em></td>
            <td>Admin, Craftsman</td>
            <td>Streams signed Dompdf voucher with barcodes for factory floor printing.</td>
        </tr>
    </table>
</div>

<div class="row" style="margin-bottom: 0;">
    <div class="col-6">
        <div class="card-success">
            <span class="card-title">Helper Lookups for Fast Mobile UI</span>
            <p><code>GET /helpers/products-by-category</code>, <code>GET /helpers/designs-by-product</code>, and <code>GET /helpers/subcategory-design</code> enable instantaneous cascading selectors in mobile apps.</p>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-primary">
            <span class="card-title">Sanctum Token Scope Defense</span>
            <p>Craftsmen attempting to view purchase vouchers not assigned to their <code>craftman_code</code> are immediately intercepted with HTTP 403 Forbidden via the <code>$scopeFilter</code> closure.</p>
        </div>
    </div>
</div>
';
$html .= '</div>';
$html .= '<div class="page-break"></div>';

// =============================================================================
// PAGE 10: REAL-TIME INFRASTRUCTURE, INTEGRATIONS & MASTER MATRIX
// =============================================================================
$html .= '<div class="page-container">';
$html .= pageHeader(10, "Real-Time Infrastructure, Integrations & Master Matrix", "SYSTEM INTEGRATION & ROADMAP");

$html .= '
<div class="row">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">1. Real-Time WebSocket Infrastructure (Laravel Reverb)</span>
            <p>Arihanth ERP integrates <strong>Laravel Reverb 1.0</strong> for sub-second, bi-directional event broadcasting across all web panels and mobile devices without HTTP polling:</p>
            <div class="step-box">
                <strong>Direct & Group Messaging:</strong> Broadcast channel <code>conversation.{id}</code> and <code>group.{id}</code> allows instant buyer-to-admin and admin-to-craftsman messaging.
            </div>
            <div class="step-box">
                <strong>Private Notification Channel:</strong> Channel <code>user.{type}.{id}</code> streams real-time status changes, allocation alerts, and approval notices.
            </div>
            <div class="step-box">
                <strong>Unified Guard Broadcast Authorization:</strong> <code>routes/channels.php</code> authorizes both web session cookies and Sanctum API Bearer tokens seamlessly.
            </div>
        </div>
        <div class="card-gold">
            <span class="card-title">2. Push Notification Engine: Google FCM & Apple VoIP</span>
            <p>Mobile applications receive urgent workshop dispatch and video consultation alerts via a dual-engine architecture:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><strong>Google Firebase Cloud Messaging (FCM):</strong> Delivers background push notifications for order allocations and status transitions.</li>
                <li><strong>Apple Push Notification Service (APNs VoIP):</strong> Delivers high-priority wake-up notifications for incoming Agora video calls, bypassing iOS sleep state.</li>
            </ul>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-success">
            <span class="card-title">3. Video / Audio Consultation via Agora RTC SDK</span>
            <p>Integrated via <code>cyberdeep/laravel-agora-token-generator</code>:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li>Enables live, high-definition CAD design review sessions between B2B Buyers and Master Craftsmen.</li>
                <li>Dynamic token generation: <code>GET /api/common/chat/agora-token</code> and <code>GET /api/common/meetings/{room_id}/token</code>.</li>
                <li>Supports multi-party video conferencing, screen sharing of 3D jewelry renders, and remote quality audits.</li>
            </ul>
        </div>
        <div class="card">
            <span class="card-title">4. Enterprise Security, Auditing & Login Logs</span>
            <p>Every authentication event is recorded in the <code>login_logs</code> table capturing:</p>
            <ul style="margin: 0; padding-left: 12px; font-size: 6.8pt;">
                <li><code>user_id</code>, <code>user_type</code> (Polymorphic), and <code>ip_address</code>.</li>
                <li><code>user_agent</code>, <code>device_type</code>, and login timestamp.</li>
                <li>Automatic session timeout enforcement after 120 minutes of inactivity.</li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <span class="card-title">5. Master Architectural Capabilities Matrix Across All 7 Panels</span>
    <table class="data-table">
        <tr>
            <th style="width: 16%;">System Capability</th>
            <th style="width: 12%;">SuperAdmin</th>
            <th style="width: 12%;">Admin</th>
            <th style="width: 12%;">Buyer</th>
            <th style="width: 12%;">Key User</th>
            <th style="width: 12%;">Sales User</th>
            <th style="width: 12%;">Craftsman</th>
            <th style="width: 12%;">Staff</th>
        </tr>
        <tr>
            <td><strong>Work Order Create</strong></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
        </tr>
        <tr>
            <td><strong>Work Order Allocate</strong></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
        </tr>
        <tr>
            <td><strong>Work Order Accept/Reject</strong></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-amber">Delegated</span></td>
        </tr>
        <tr>
            <td><strong>Purchase Order Create</strong></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
        </tr>
        <tr>
            <td><strong>PO Partial Processing</strong></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
        </tr>
        <tr>
            <td><strong>Approval / Signoff</strong></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-green">Full</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
        </tr>
        <tr>
            <td><strong>Undo / Return Flow</strong></td>
            <td><span class="badge badge-green">Uncapped</span></td>
            <td><span class="badge badge-amber">OTP-Gated</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
            <td><span class="badge badge-gray">No</span></td>
        </tr>
        <tr>
            <td><strong>Agora Video Meetings</strong></td>
            <td><span class="badge badge-green">Yes</span></td>
            <td><span class="badge badge-green">Yes</span></td>
            <td><span class="badge badge-green">Yes</span></td>
            <td><span class="badge badge-green">Yes</span></td>
            <td><span class="badge badge-green">Yes</span></td>
            <td><span class="badge badge-green">Yes</span></td>
            <td><span class="badge badge-gray">No</span></td>
        </tr>
    </table>
</div>

<div class="row" style="margin-bottom: 0;">
    <div class="col-6">
        <div class="card-primary">
            <span class="card-title">Production Deployment Recommendations</span>
            <p>For high-concurrency manufacturing deployments: (1) Utilize Redis for cache and Reverb WebSocket backend; (2) Run queue workers for async PDF watermarking via <code>php artisan queue:work</code>; (3) Enforce HTTPS with TLS 1.3 across all RESTful API endpoints.</p>
        </div>
    </div>
    <div class="col-6-right">
        <div class="card-gold">
            <span class="card-title">Future Engineering Roadmap</span>
            <p>Phase 3.0 integrations include: (1) AI computer vision for automated diamond clarity grading and flaw detection; (2) Direct integration with automated laser hallmark engraving machines; (3) Real-time gold market spot-price live hedging via financial API feeds.</p>
        </div>
    </div>
</div>
';
$html .= '</div>';

$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$outputPdfPath = 'Arihanth_Jewellers_ERP_Complete_Study_10_Pages.pdf';
file_put_contents($outputPdfPath, $dompdf->output());

echo "SUCCESS! Generated PDF file: " . $outputPdfPath . "\n";
