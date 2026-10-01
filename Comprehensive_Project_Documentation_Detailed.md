# Comprehensive Project Architecture & Feature Documentation

**Prepared for: Development Team, Client, and Project Management**

This document serves as the master blueprint for the system's features, detailing the workflows, administrative actions, and API capabilities across all six primary user roles. *(Note: The Repair module is intentionally excluded from this documentation. Final approvals for designs and products are restricted strictly to the SuperAdmin and Admin roles).*

---

## 1. SuperAdmin Login
The SuperAdmin role is the highest tier of access, providing unrestricted control over the entire platform's data, users, and core configurations.

### A. Dashboard & Master Aggregation
* **Details All Master View:** The SuperAdmin dashboard includes a specialized "Details All" module. This acts as a centralized data aggregator where the SuperAdmin can filter by Business Partner (BP) Code or User Type. It instantly pulls up a comprehensive view of that user's **Accepted Designs** and **Favorites**, allowing for deep analytics into client preferences without navigating through multiple pages.
* **Global Search Utility:** A platform-wide, deep-search tool allowing the SuperAdmin to search across all database tables to instantly locate Work Orders, Purchase Orders, users, or specific products using keywords or ID numbers.

### B. Work Order Lifecycle Management
The SuperAdmin has absolute control over the manufacturing supply chain via the Work Order module:
* **Creation & Bulk Uploads:** Work Orders can be created individually via a detailed form. For large operations, the SuperAdmin can download a standardized template, populate it, and use the **Bulk Upload** feature to ingest hundreds of orders at once.
* **Allocation & Smart Suggestions:** To assign work to the factory floor, the SuperAdmin uses the **Allocate** feature. To optimize this process, the system offers an **Allocate Suggestion** tool which intelligently recommends the most appropriate craftsman based on their current workload and historical efficiency.
* **Bulk Operations:** SuperAdmins can select multiple pending orders and use **Bulk Allocate** to assign them to a single craftsman, or use **Bulk Approve** and **Bulk Complete** to rapidly push orders through the final stages of the supply chain.
* **Corrections & Security (Undo/Return):** If an error occurs, the SuperAdmin can revert an order's status using the **Undo** function, or initiate a **Process Return**. Both of these high-level destructive actions are secured by a strict Two-Factor Authentication (2FA) mechanism requiring an **OTP sent via SMS or WhatsApp**.
* **Export & Print:** The entire work order grid can be exported to Excel/CSV for offline auditing, and detailed manufacturing slips can be printed individually or via **Bulk Print**.

### C. Purchase & Stock Order Modules
* **Purchase Orders (PO):** The SuperAdmin reviews POs submitted by Buyers. They have the authority to Approve or Reject these requests. Once approved, the PO is pushed into the manufacturing pipeline.
* **Stock Orders:** The system allows the SuperAdmin to create stock requests (raw materials), allocate them directly to specific craftsmen, and monitor their status until marked as Complete.

### D. Security, Tracking, and User Credentials
* **IP Tracking & Login Analytics:** The `UserCredentialController` powers a strict security dashboard where the SuperAdmin can monitor every user in the system. It tracks the total number of successful logins, timestamps the last login, and crucially, logs the **IP Address** used for every session to monitor for unauthorized access.
* **Freeze Account Protocol:** If suspicious activity is detected or a contract is terminated, the SuperAdmin can instantly suspend an account using the **Freeze Account** tool, immediately revoking their access to the Web Panel and API.
* **KYC Pending Approvals:** New user registrations undergo a Know Your Customer (KYC) check. The SuperAdmin reviews submitted legal documentation and manually approves or rejects the account.

### E. Communication & Product Management
* **Meeting Manager:** An integrated scheduling tool to log and track internal meetings with staff or external meetings with buyers.
* **Company Contacts & Updates:** The SuperAdmin manages the official internal contact directory and uses the **Updates** module to broadcast system-wide announcements to all user dashboards.
* **Catalogue & Design Approvals:** The SuperAdmin manages all product categories and subcategories. When a user submits a custom design, **only the SuperAdmin or Admin** has the authority to review the specifications and grant final approval for it to be added to the catalogue.

---

## 2. Admin Login
The Admin role is designed for day-to-day operational managers. It mirrors the SuperAdmin's interface but lacks the ability to perform top-level destructive actions (like deleting a SuperAdmin).

### A. Operations & Order Tracking
* Admins have full access to the **Details All** dashboard and **Global Search**.
* They manage the daily flow of **Work Orders** (including Bulk Allocations and Allocate Suggestions), **Purchase Orders**, and **Stock Orders**.

### B. Security & Catalogue Processing
* Admins actively monitor **User Credentials** (IP Tracking and Login Counts) to ensure system security.
* They process incoming **KYC Applications**.
* Admins share the authority with SuperAdmins to review, edit, and **Approve/Reject Custom Designs** submitted by buyers.

---

## 3. Buyer Login
The Buyer role represents the external clients, wholesalers, or retailers placing orders into the system.

### A. Dashboard & Communications
* **Buyer Dashboard:** Features a restricted **Global Search** allowing the buyer to search exclusively through their own approved designs and order history.
* **Meetings:** Buyers can view upcoming scheduled meetings with the administration and request appointments.

### B. Order Placement & Tracking
* **Purchase & Stock Orders:** Buyers can browse the global catalogue and seamlessly generate Purchase Orders. They can also submit requests for Stock Orders.
* **Work Order Tracking:** Once a Purchase Order is approved, the Buyer can track its real-time manufacturing progress (Pending -> In Process -> Ready -> Delivered). Upon physical delivery, the buyer must acknowledge and approve the completed order in the system.

### C. Catalogue & Personalization
* **Favorites System:** Buyers can curate their own experience by adding specific products to their **Favorites** list for rapid re-ordering in the future.
* **Custom Design Submission:** Buyers can upload images, blueprints, and text specifications to request a custom product. This request remains in a "Pending" state until an Admin/SuperAdmin officially approves it.

---

## 4. Key User Login
Key Users function as regional or organizational managers who oversee multiple operations under a specific Business Partner (BP) Code.

### A. Elevated Organizational Tracking
* **Global Order Visibility:** Key Users can track, monitor, and export all Work Orders, Purchase Orders, and Stock Orders associated with their organization's BP Code, providing a high-level view of their supply chain.
* **Catalogue Management:** They possess the ability to browse the catalogue, manage organizational Favorites, and submit Purchase Orders on behalf of their associated buyer accounts.

---

## 5. Craftsman Login
Craftsmen are the factory managers or lead manufacturers responsible for executing the allocated Work Orders.

### A. Production Floor Management
* **Order Acknowledgment:** When an Admin allocates a Work Order or Stock Order to a Craftsman, it appears in their dashboard. The Craftsman must formally **Accept or Reject** the allocation.
* **Manufacturing Workflow:** As production begins, the Craftsman updates the order status to "In Process". Once finished, they change the status to "Craftsman Completed" and are required to **Upload Proof** (photos of the finished product) directly into the system.
* **Bulk Operations:** To handle high volume, Craftsmen can select multiple Purchase Orders or Stock Orders and use **Bulk Complete** to mark them all as finished simultaneously.
* **Print Worksheets:** Craftsmen can generate and print detailed manufacturing worksheets (Single & Bulk Print) for their factory floor.

### B. Dashboard & Staff Management
* **Favorites:** Craftsmen can bookmark frequently used product specifications into their Favorites list.
* **Meetings:** They can track upcoming production meetings scheduled by the Admin.
* **Craftsman Staff Creation:** The Craftsman can independently create sub-accounts ("Craftsman Staff") for their individual factory workers and assign specific Work Orders to them.

---

## 6. Craftsman Staff Login
These are highly restricted sub-accounts created for factory floor workers.

* **Task Execution:** Craftsman Staff can only view the specific Work Orders explicitly assigned to them by their lead Craftsman.
* **Status Updates:** They are responsible for updating their assigned tasks to "In Process" and "Completed", and uploading the required photographic proof.
* **Restrictions:** They have absolutely no access to financial data, cannot allocate orders, cannot access global search, and cannot view meeting schedules.

---

## 7. API Architecture & Mobile Features
The system features a robust REST API designed to power mobile applications and third-party integrations. The endpoints are strictly protected by role-based access control.

### A. Universal Auth & Push Notifications (System-Wide)
* **Authentication:** The `UniversalAuthController` manages secure JWT/Token generation, Login, Logout, and OTP-secured Password Resets for all mobile app users regardless of their role.
* **Push Notifications (`NotificationController`):** A centralized API integration for Push Notifications (via FCM/APNS). The system actively pushes alerts to users' mobile devices for critical events:
  * Buyers receive push notifications when their Custom Design is approved or when their Work Order is out for delivery.
  * Craftsmen receive immediate push notifications when a new Work Order or Stock Order is allocated to them.
  * Admins receive push notifications for new KYC submissions or high-priority Purchase Orders.

### B. SuperAdmin & Admin APIs
* **Administrative Control:** Full API endpoints allowing Admins to Create/Update/Delete users and products remotely.
* **Order Overrides:** Mobile capabilities to Bulk Allocate orders, override statuses, and approve custom designs on the go.
* **Analytics Fetching:** Endpoints to retrieve IP tracking logs, login counts, and the "Details All" aggregates directly to an admin mobile dashboard.

### C. Buyer & KeyUser APIs
* **Mobile Commerce:** Endpoints providing paginated scrolling through the product catalogue, filtering, and adding items to the mobile Favorites list.
* **Order Submission & Tracking:** Buyers can submit custom design photos directly from their phone's camera roll via the API, place Purchase Orders, and poll endpoints for real-time manufacturing tracking.

### D. Craftsman APIs
* **Mobile Factory Floor:** The Craftsman API is heavily optimized for mobile workflow. Factory workers can use their phones to Accept allocations, tap to change order statuses, and use the mobile camera to snap and instantly upload "Completion Proof" images directly to the server.
* **Stock Acknowledgment:** Remote endpoints to confirm the receipt of physical stock orders at the factory.
