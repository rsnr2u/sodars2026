# Shared Design System Guidelines

We standardize on a single, professional enterprise ERP visual identity.

---

## 1. Tokens
All layouts utilize system tokens defined in `@sodars/design-system`:
* **Typography**: Default to modern sans-serif fonts (e.g. Inter or Outfit).
* **Grid**: 8px spatial grid guidelines (padding, margin, heights).
* **Colors**: HSL variables supporting Light, Dark, and System modes.

## 2. Core Primitive Components
Every portal imports standard UI components exclusively from `@sodars/design-system`:
* `Button`: States (primary, secondary, danger, warning).
* `DataGrid`: Virtualized list tables utilizing `TanStack Table`.
* `Charts`: Apache ECharts chart cards wrappers.
* `Forms`: Validation adapters with inputs mapping.
