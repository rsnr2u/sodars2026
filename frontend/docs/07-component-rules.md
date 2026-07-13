# Component Rules

This document outlines the strict styling, structural, and state guidelines that all SODAARS components must follow.

---

## Rule 1: Never Use Raw Tailwind Colors
Avoid raw Tailwind color names or arbitrary hex values inside visual markup. All colors must resolve from custom theme variables.

* ❌ `className="bg-green-500 text-white border-slate-300"`
* ✅ `className="bg-primary text-white border-border"`

---

## Rule 2: Never Hardcode Spacing
Do not use arbitrary padding, margins, or sizing classes. All spatial layout margins must align to SODAARS spacing tokens.

* ❌ `className="p-4 m-3 gap-5"`
* ✅ `className="p-lg m-md gap-xl"`

---

## Rule 3: No Module Cross-Imports
Atomic components inside `packages/design-system` must be strictly self-contained. They are prohibited from importing pages, router components, or business modules. They may only import from the design system, config, utils, assets, or constants packages.

---

## Rule 4: No Page-Specific Reusable UI
If a component is reused across more than one view page, it is prohibited from living in a local `components/` page folder. It must immediately be refactored and moved to `@sodars/design-system`.

---

## Rule 5: Support All Visual States
Every interactive element or container block must natively configure and support:
- **Loading State**: Shimmer loading skeletons
- **Disabled State**: Blocked interaction styles
- **Error State**: Validation alerts
- **Empty State**: Custom placeholder cards
- **Success State**: Verified check marks

---

## Rule 6: Input Accessibility
Every interactive component must provide complete inputs support:
- Keyboard navigation (tab focus, Enter/Space action triggers)
- Touch support (responsive padding sizes)
- Hover and focus focus-ring boundaries
- Standard ARIA labels

---

## Rule 7: Data Table Capabilities
Any data grid or list view must include default slot support for:
- Column sorting carets
- Contextual filter tags
- Search query highlighting
- Column visibility managers
- Custom pagination rows
- View density scale selectors (relaxed, normal, compact)
- CSV/Excel export triggers
- Row checkboxes and bulk action bars
