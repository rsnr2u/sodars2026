# Module SDK Guidelines

Each bounded context corresponds 1-to-1 to a frontend module.

---

## 1. Structure
Every module exports a bootstrapper implementing the `SodarsModule` contract:
```typescript
interface SodarsModule {
  name: string;
  registerRoutes(): RouteConfig[];
  registerMenus(): MenuItem[];
  registerWidgets(): WidgetConfig[];
  registerPermissions(): string[];
}
```

## 2. Bootstrapping
When the application starts:
1. The app boots the `ModuleSDK` engine.
2. The SDK imports modules dynamically.
3. Routes, menus, and widgets are injected into layout trees.
