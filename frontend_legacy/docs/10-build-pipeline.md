# Build Pipeline Guidelines

The frontend monorepo utilizes Turborepo to configure build tasks and caching schedules.

---

## 1. Commands
Execute the following pipeline scripts at the root level of the `frontend/` workspace:
* `npm run dev`: Boots Vite development servers for portals concurrently.
* `npm run build`: Resolves TS type-checking and builds production assets.
* `npm run lint`: Validates ESLint and Prettier compliance.
* `npm run test`: Runs unit tests across all portals.

## 2. Remote Caching
Turborepo caches build outputs (like `.vite` assets and typescript definitions) dynamically, accelerating CI/CD build actions.
