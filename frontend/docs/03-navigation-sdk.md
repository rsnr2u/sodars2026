# Navigation SDK Guidelines

Sidebar menus must never be hardcoded inside UI components.

---

## 1. Registration
Modules register navigation links during initialization:
```typescript
NavigationSDK.registerMenuItem({
  id: 'campaigns',
  label: 'Campaigns',
  path: '/campaigns',
  icon: 'MegaPhone',
  permission: 'campaigns.view',
  priority: 20
});
```

## 2. Rendering
The sidebar layout fetches dynamic menu lists from `NavigationSDK`, checks the logged-in user permissions, sorts them by priority index, and lists the items automatically.
