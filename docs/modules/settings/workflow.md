# Settings Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic sequences for loading system configurations, updating branding parameters, toggling maintenance gates, and executing backups.

---

## Scope

This document specifies step-by-step processes, verification steps, and cache operations for:
* Bootstrapping settings and resolving feature flags.
* Modifying profile details, tax metrics, and API secrets.
* Triggering maintenance mode.
* Running SQL backup generation and rollbacks.

---

## Business Rules

### 1. Workflow: System Boot & Configuration Load
* **Actor**: System (Laravel Application).
* **Steps**:
  1. Server receives an incoming request.
  2. Boot service queries the in-memory cache (Redis) for `sodars_global_settings`.
  3. If cache hit:
     * System loads settings variables array into memory config parameters.
  4. If cache miss:
     * System queries the `system_settings` database table.
     * Decrypts values where `is_encrypted = 1` using the app's cryptographic key.
     * Writes settings to Redis cache, then loads parameters into memory config.
  5. Boot service queries `feature_flags` to evaluate active/inactive route status lists.
  6. Application continues routing.

---

### 2. Workflow: Configuration Update & Cache Refresh
* **Actor**: Super Admin.
* **Steps**:
  1. Admin opens Settings panel, updates a configuration parameter (e.g. changes `default_markup_percentage` from 20 to 18).
  2. System checks permissions:
     * *Validation*: User role must be Super Admin.
  3. System validates input bounds:
     * *Validation*: New markup value must be $\le$ 20.
  4. System updates the target database row in `system_settings`.
  5. System triggers audit log:
     * Inserts record into `system_logs` logging the user, IP coordinates, change type, and old/new value snapshots.
  6. System flushes the Redis key:
     * Next boot query is forced to query the database and rebuild cache files automatically.

---

### 3. Workflow: Toggle Maintenance Mode
* **Actor**: Super Admin.
* **Steps**:
  1. Super Admin toggles "Maintenance Mode" status switch.
  2. System updates configuration settings record `maintenance_mode_active = 1`.
  3. Flushes cache.
  4. Global router interceptor logic:
     * Checks maintenance status on every request.
     * If active:
       * *Route rule*: Bypasses check if request routes to `/api/v1/admin/*` or `/api/v1/auth/*` (allowing admins and providers to access their portals).
       * *Block rule*: Redirects all public marketplace requests to a static maintenance page showing a placeholder warning banner.

---

### 4. Workflow: Backup Execution
* **Actor**: System (Cron task) / Super Admin.
* **Steps**:
  1. User triggers **Run Backup Now** or daily cron fires at 02:00 AM.
  2. System calls SQL Dump utility. Compiles the MySQL database schema and records dataset into a compressed `.sql.gz` archive.
  3. System streams the archive file to the secure S3 backups bucket directory.
  4. System logs backup details (file path, file size, timestamp) in `system_backups`.
  5. System triggers a confirmation email to the Super Admin.

---

## Future Scope

* **Automatic Drift Detection**: Systems notifying administrators if `.env` database parameters vary from config snapshots values (deferred to V2).
