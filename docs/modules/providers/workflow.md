# Provider Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for the Provider module, tracing provider actions from signup to deactivation.

---

## Scope

This document specifies the step-by-step sequences, logic rules, and validation checks for:
* Provider Registration & Login.
* Branch Assignment & Profile Audit/Approval.
* Uploading compliance documentation.
* Subscribing and renewing tier limits.
* Adding staff permissions.
* Disabling/Enabling marketplace display settings.
* Suspensions and account termination.

---

## Business Rules

### 1. Workflow: Provider Registration & Branch Assignment
* **Actor**: Applicant / Display Owner.
* **Steps**:
  1. Applicant enters: Company Name, Business Address, City, State, Tax Registration Number (e.g., GSTIN), and primary contact email/phone.
  2. The system checks database parameters:
     * *Validation*: Tax Registration Number must be globally unique.
     * *Validation*: Email must not be taken.
  3. System matches the provider's registered address city against the `branch_coverage_cities` list.
     * *Routing Rule*: Mapped to the matching `branch_id` as their default managing branch.
     * *Fallback*: If no city matches, assigned to the Head Office default branch.
  4. System inserts a `providers` table record in `draft` status, and creates the primary User account in `provider_admin` role.

---

### 2. Workflow: Compliance Document Upload & Profile Audit
* **Actor**: Provider Admin / Branch Manager.
* **Steps**:
  1. Provider Admin logs in and uploads business identity files (PDF/PNG format).
  2. Files are uploaded directly to the secure AWS S3 bucket, and record entries are saved in the `provider_documents` table in `pending` status.
  3. Profile status shifts to `Pending Verification`.
  4. The assigned Branch Manager receives a dashboard alert:
     * Manager reviews documents.
     * Manager clicks **Approve** -> Documents shift to `approved`; Provider status shifts to `verified`.
     * Manager clicks **Reject** -> Document state shifts to `rejected`. Profile shifts to `draft`. Manager must submit text comments describing corrective edits.

---

### 3. Workflow: Subscription Activation & Renewal
* **Actor**: Provider Admin / Stripe Gateway.
* **Steps**:
  1. Provider Admin views subscription options and clicks selection (e.g., Standard Tier, max 20 screens).
  2. Checkout initiates. Stripe webhook returns payment validation success.
  3. System inserts record in `provider_subscriptions` table:
     * Sets `starts_at` to current time.
     * Sets `ends_at` to current time + 30 days.
     * Sets `is_active = 1`.
     * Sets `max_active_screens = 20`.
  4. Renewal runs on automated daily cron tasks. If expiration date passes:
     * Subscription shifts to `is_active = 0`.
     * System checks active screens. If provider has 5 screens, it auto-disables screens beyond the Free Tier limit (only the 2 oldest screens remain active; the rest shift to `inactive` status).

---

### 4. Workflow: Toggle Marketplace Visibility
* **Actor**: Provider Admin.
* **Steps**:
  1. Admin toggles the "Enable Marketplace Listings" switch in settings.
  2. System changes `enable_marketplace` flag in `provider_settings`.
  3. If changed to `0` (disabled):
    * The system hides all screen listings owned by this provider from public maps. Active campaigns continue to run, but new bookings are blocked.

---

### 5. Workflow: Account Suspension & Deactivation
* **Actor**: Branch Manager / Super Admin.
* **Steps**:
  1. Auditor selects Provider Profile and triggers **Suspend** or **Deactivate**.
  2. System shifts status to `suspended` or `deactivated` (soft delete `deleted_at` timestamp applied).
  3. All screen inventory owned by this provider immediately has marketplace visibility revoked.

---

## Future Scope

* **Auto-Onboarding verification**: API verification of tax registration numbers against government databases to auto-verify credentials.
