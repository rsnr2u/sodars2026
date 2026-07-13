# Routing Guidelines

All application views use `@tanstack/react-router` for full, type-safe path navigation.

---

## 1. Type-Safe Links
* Links must use typed router targets to ensure static validation fails if a path changes.
* Enforce Route loaders to fetch mandatory API data before route resolution.

## 2. Authentication Guards
Route definitions include `beforeLoad` middleware handlers that verify active authentication tokens in `authStore`. Unauthenticated requests redirect automatically to `/login`.
