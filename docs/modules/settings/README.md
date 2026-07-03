# Module: Settings

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to introduce the Settings module, which acts as the single source of truth for all configurable variables, business limits, feature flags, and API gateway keys across SODARS.

---

## Scope

This document specifies:
* Centralized settings routing.
* The Configuration Hierarchy.
* Settings inheritance and overrides rules.
* Config versioning and safety locks.

---

## Business Rules

### 1. Configuration Hierarchy
To keep parameters organized, settings follow a clean inheritance schema:

```text
Global Settings (Super Admin)
  └── Branch Settings (Branch Manager overrides, e.g. local markup)
        └── Provider Settings (Display owner profile parameters)
```

* **Global Settings**: Core network properties (system name, global maximum markup limit of 20%, global tax rules, AWS/SMTP API secrets). Managed only by Super Admin.
* **Branch Settings**: Regional variables (branch contact details, localized branch tax rates, custom markups). Managed by local Branch Managers.
* **Provider Settings**: Business settings specific to the display owner portal (bank ledgers, email notifications preferences).
* **Environment Settings**: Hard configurations loaded from secure server environments (e.g. `.env` file files). These are read-only and can never be written or altered from user interfaces.

---

### 2. Settings Inheritance Rules
* Branch configurations inherit values from Global Settings by default.
* If a Branch Manager sets a custom regional variable (e.g., local markup of 15%), this value overrides the global setting for assets mapped under that `branch_id`.
* *Audit Lock*: Branch overrides cannot violate global ceiling limits (e.g., branch markup override must be $\le$ global maximum setting).

---

## Future Scope

* **White Labeling & Dynamic Styles**: Allowing multi-tenant franchise branch operators to load custom CSS themes and branding graphics (deferred to V2).
* **HashiCorp Vault Integration**: Standardized integration with external vault engines to safeguard credentials (deferred to V2).
