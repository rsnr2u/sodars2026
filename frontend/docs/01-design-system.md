# SODAARS Design System

## 1. Color Palette Custom Tokens
We use dedicated HSL token mappings in Tailwind CSS v4:
- **Primary / Dark Emerald** (`var(--primary)` / `#0B5D4B`): Brand primary container headers and backgrounds.
- **Secondary / Emerald** (`var(--secondary)` / `#10B981`): Action accents and hover indicators.
- **Accent / Mint** (`var(--info)` / `#6EE7B7`): System status info tags.
- **Warning / Gold** (`var(--warning)` / `#F4B400`): In-review/caution alerts.
- **Success / Green** (`var(--success)` / `#16A34A`): Confirmed/verified compliance status tags.
- **Error / Red** (`var(--danger)` / `#DC2626`): Expired compliance or rejection notifications.

## 2. Shared Atomic UI Components
All custom screens consume modular, atomic items exported by `packages/ui`:
- **Button**: Custom variant controls (primary, outline, danger, success) with size and loading spinner states.
- **Badge**: Status pill identifiers with pulse support.
- **Card**: Solid/glassmorphic grid container box.
- **Input**: Form inputs with validation labels.
- **Select**: Dropdown selectors.
- **Drawer**: Slide-out overlay panels with backdrop-blur.
- **Dialog**: Alert confirmation dialogs.
- **Skeleton**: Shimmer loading block states.
- **Empty State**: Call-to-action placeholder widgets.
