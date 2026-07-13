# SODAARS UI Philosophy

This document outlines the visual principles, layout guidelines, and structural design choices that dictate SODAARS user interfaces.

---

## 1. Core Visual Principles

### Whitespace First
Every layout must prioritize readability and separation. Use generous margins and padding to let data breath. Never overcrowd the interface.

### Keyboard First
All primary actions, search filters, modal closures, and navigation options must be controllable by keyboard shortcuts.

### Desktop First, Mobile Responsive
1. **Desktop UI**: First class enterprise grid views, dense interactive datagrids, sidebar layout.
2. **Tablet UI**: Collapsible sidebar, fluid scroll grids.
3. **Mobile UI**: Full-screen drawers, stackable form fields, grid cards replacing tables.

---

## 2. Layout Constraints

- **Maximum 3 Core Colors**: Avoid rainbow colors. Primary (Dark Emerald), Accent/Secondary (Emerald), Gray/Slate shades.
- **Maximum 2 Shadows**: Consistent elevation styling using only two defined shadow variables (`--shadow-sm` and `--shadow-md`).
- **Maximum 1 Primary CTA**: Every screen or dialog box must contain at most one dominant primary action button to focus user choices.
- **No Unnecessary Gradients**: Visual blocks must remain flat or use subtle solid borders.
- **No Glassmorphism Everywhere**: Maintain clean, professional card structures suited for a corporate ERP.

---

## 3. Screen Structure Architecture
Every page view must maintain this unified logical zones hierarchy:
1. **Header Area**: Breadcrumb trail, contextual Title block, notification alerts, and Primary/Secondary layout actions.
2. **Filters Area**: Chip filters, date pickers, column managers, search controls.
3. **Content Area**: Fluid viewport grid, datagrid table, loading shimmer, or empty state.
4. **Help Area**: Contextual keyboard shortcut cheat sheet or inline documentation tooltips.
