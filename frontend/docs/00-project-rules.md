# SODAARS UX-First Development Rules

## 1. The UX Freeze Rule
This project operates under a strict UX-first methodology. All backend, database, and repository layers are frozen until the UX and frontend layouts are completed and visually approved by the user.

### Banned During Phase 1:
- ❌ Axios / Fetch requests
- ❌ TanStack Query / RTK Query / SWR
- ❌ Zustand / Redux / Context state stores
- ❌ Database migrations / schemas
- ❌ Laravel service, repository, or API layers
- ❌ Authentication / Identity integrations

### Required During Phase 1:
- ✅ High fidelity layouts and responsive interfaces
- ✅ Local state management and realistic mock data
- ✅ Custom components with interactive states
- ✅ Standardized loading, empty, and error templates
- ✅ Keyboard navigation shortcuts and accessibility

## 2. General Style Guidelines
- Use CSS Variables in Tailwind CSS v4 to theme components.
- Do not hardcode hex color strings in component markup.
- Prefer explicit responsive styles (e.g. `sm:`, `md:`, `lg:`) to verify layouts on all viewports.
- Keep interactive animations subtle and apply them only after page/module approval.
- **Component Reuse Rule**: No component may be created specifically for a single page unless it is proven to be page-specific. All reusable UI must live inside `packages/design-system`.

## 3. Component Hierarchy Constraints
- **No Page Direct Atom Usage**: No page or workspace view component may directly invoke atomic level visual items. All components must traverse the design levels logically.
  ```
  Page/Workspace -> Pattern -> Template -> Organism -> Molecule -> Atom
  ```
  Page files are prohibited from directly rendering components like `<Button />` or `<Input />`. They must compose templates or patterns instead.

