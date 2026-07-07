# Widget SDK Guidelines

Dashboards are visual grids rendering dynamic widgets registered by individual modules.

---

## 1. Widget Registration
A module registers dashboard widget configs:
```typescript
WidgetSDK.registerWidget({
  id: 'operations_overtime_ratio',
  name: 'Technician Overtime Ratio',
  component: React.lazy(() => import('./widgets/OvertimeRatioWidget')),
  defaultLayout: { w: 4, h: 2 },
  permissions: ['operations.reports.view']
});
```

## 2. Widget Loader
The Dashboard layout reads widget parameters from the user's dashboard preferences, authorizes permissions via `PermissionGate`, and mounts the components dynamically.
