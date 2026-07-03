# Settings Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, permissions constraints, security mandates, and cache validation requirements for Settings.

---

## Scope

This document specifies the access control matrix, configuration encryption rules, and system behavior gates.

---

## Business Rules

### 1. Permission Gates & Visibility
* **Super Admin Only**: Read/write access to system settings, templates, API secrets, backups, and feature flags is restricted exclusively to the Super Admin (Head Office) role.
* **Branch Manager Limits**: Branch Managers can view and modify regional branch properties (assigned to their `branch_id`), but have zero access to query or edit global system configurations.
* **Provider Limits**: Providers are blocked from querying, editing, or viewing any global system settings.

---

### 2. Security & Encryption Mandates
* **Encryption on Write**:
  * The system must encrypt sensitive configuration values using the application's central AES-256 key before database insertion.
  * Encrypted value indicators: `is_encrypted = 1` in database settings rows.
* **Sensitive Values List**:
  * API Keys (Stripe, Google Maps).
  * SMTP passwords.
  * SMS and WhatsApp gateway auth secrets.
  * Storage credentials (AWS S3 Secret Access Keys).
  * JWT auth keys and OAuth secrets.
* **UI Masking**: API keys, credentials, and passwords must never be returned in plain text on client screens. The API must return masked characters (`••••••••`).

---

### 3. Feature Flags & Maintenance Mode
* **Feature Flag Routing**:
  * If a feature flag is set to `is_enabled = 0`, the system router must block access to matching endpoints (returning HTTP 404 Not Found or 403 Forbidden).
* **Maintenance Mode**:
  * If maintenance mode is active:
    * Public search, maps, and marketplace checkout pages are blocked, rendering a static holding page.
    * Portals (`/admin`, `/provider`) bypass the block to allow operations.

---

### 4. Cache & Env Protection
* **Cache Rebuild Policy**: System settings updates must flush corresponding Redis cache tags instantly.
* **Env Protection**: System `.env` environment variables cannot be modified from the database UI dashboard. Database overrides (`is_env_override = 1`) must only be configured in local config files.
* **Configuration Audit**: Changes to database setting rows must log old and new value snapshots to `system_logs` to maintain an audit trail.

---

## Future Scope

* **Integration with external Secrets Managers**: Migrating encrypted fields directly to AWS Secrets Manager or HashiCorp Vault (deferred to V2).
