# State Management Guidelines

Application state is managed by micro-Zustand stores rather than a single monolith store.

---

## 1. Store divisions
Each store owns a single, isolated domain state:
* `authStore`: User identity token and details.
* `tenantStore`: Active `organization_id` value.
* `themeStore`: UI display mode (`light`, `dark`, `system`).
* `notificationStore`: Websocket alerts and messages feed.
* `sidebarStore`: Sidebar toggle states.
* `permissionStore`: Loaded privilege lists.

## 2. Invariants
Stores must never hold raw business data meant for temporary UI listings. That data belongs exclusively to TanStack Query server state.
