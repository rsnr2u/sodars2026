# Folder Conventions

Every feature module inside a portal (like `apps/admin/src/modules/campaigns/`) must follow a uniform, predictable directory layout:

```text
modules/campaigns/
├── module.ts (module SDK registration)
├── components/ (module-private UI components)
├── pages/ (TanStack router page layouts)
├── hooks/ (queries and mutations adapters)
├── api/ (custom endpoints client integrations)
├── types/ (module-private TypeScript interfaces)
├── schemas/ (Zod validation schemas for forms)
└── routes/ (route definitions files)
```
This mirrors the backend bounded contexts, ensuring high predictability across codebases.
