# 06. Folder Structure

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to establish the layout of the SODARS codebase, organizing the backend Laravel API, React client applications, mobile app, and documentation into a structured repository design.

---

## Scope

This document specifies:
* Monorepo directory map.
* File organization inside the backend Laravel service.
* File organization inside client portals.
* Strict dependency boundaries between directory trees.

---

## Business Rules

### 1. Monorepo Directory Tree

```text
SODARS/ (Repository Root)
├── docs/                     # Project-wide documentation (Core & Modules)
│   ├── modules/              # Subfolders for each functional domain
│   └── ...                   # Core .md files (00 to 13)
│
├── backend/                  # Laravel REST API Application
│   ├── app/
│   │   ├── Http/             # Controllers, Middleware, Requests
│   │   ├── Models/           # Eloquent Database Models
│   │   ├── Services/         # Business Logic Layer (services, calculations)
│   │   └── Traits/           # Reusable helper attributes (UUIDs, logs)
│   ├── config/               # App configuration
│   ├── database/             # Migrations, Seeds, and Factories
│   ├── routes/               # API routes files
│   └── tests/                # Automated API Feature & Unit tests
│
├── web-clients/              # Client Portals (React + Vite/Next.js)
│   ├── admin/                # Head Office & Branch Portal application
│   ├── provider/             # Provider Dashboard application
│   ├── marketplace/          # Public Map Discovery & Customer Checkout application
│   └── shared-ui/            # Shared styles, layouts, utility services, Axios instances
│
└── mobile-app/               # React Native Expo Application (Customer & Provider tools)
    ├── src/
    │   ├── components/       # Custom React Native UI components
    │   ├── screens/          # Application screens
    │   └── services/         # API connectors and hardware hooks (Camera, GPS)
```

### 2. File Organization Conventions
* **Domain Autonomy**: Do not write PHP/Laravel blade files in the `/backend` folder that render UI. All UI is separated in `/web-clients` or `/mobile-app`.
* **Shared Assets**: Shared design tokens (color palettes, font settings, layout margins) must be imported from `web-clients/shared-ui` into the respective portals to maintain design language consistency.

---

## Future Scope

* Introduction of Docker container configurations (Dockerfiles and docker-compose.yml) at the repository root to support uniform local development sandboxing.
* Setup of CI/CD configuration files (e.g., `.github/workflows`) at the root level for automated deployments of specific subdirectories.
