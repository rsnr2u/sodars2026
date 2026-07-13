# Sprint Definition of Done (DoD)

This document defines the quality gates and checklists that must be satisfied before freezing any development sprint.

---

## 1. Code Quality
* [ ] **Compilation**: Monorepo builds completely without errors (`npm run build`).
* [ ] **Testing**: Vitest suite completes successfully (`npx turbo run test`).
* [ ] **Linting**: Linter returns zero warnings or errors (`npm run lint`).
* [ ] **Types**: TypeScript compile check completes successfully with zero warnings under strict mode parameters.
* [ ] **Cleanliness**:
  * No `console.log` statements inside feature modules.
  * No circular package imports.
  * All `TODO` or `FIXME` items resolved.

---

## 2. Architectural Adherence
* [ ] **Registries**: All UI components, routes, menus, and widgets are registered using the core `BootstrapContext` facade. No hardcoding in layout containers.
* [ ] **Contracts**: API transactions utilize shared TypeScript data models and enums mapping the backend 1-to-1.
* [ ] **Separation**: Layout packages remain decoupled from presentational components in `@sodars/design-system`.
* [ ] **Isolation**: No package imports elements outside its declared package.json dependency scope boundaries.

---

## 3. User Experience (UX) Standards
* [ ] **Responsive**: Views scale across mobile web, desktop, and tablets.
* [ ] **Accessibility**: Focus and keyboard controls behave correctly.
* [ ] **Theme**: Both Light, Dark, and System modes resolve CSS variables variables.
* [ ] **States**: Core loaders (skeletons), empty indicators, and error boundaries handle system state gracefully.

---

## 4. Diagnostics & Traceability
* [ ] **Correlation**: Requests inject Correlation and Request IDs headers.
* [ ] **Tenant context**: API requests carry selected Organization and Branch headers.
* [ ] **Telemetry**: Navigation, errors, and mutations dispatch structured tracing events.
* [ ] **Compatibility**: Frontend startup validates backend api limits constraints.

---

## 5. Documentation & Version Control
* [ ] **Changelog**: Release metrics logged.
* [ ] **Tagging**: Sprint version tag created.
* [ ] **ADR**: Design choices captured inside architectural decision records.
