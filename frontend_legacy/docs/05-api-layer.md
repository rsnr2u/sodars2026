# API Layer Guidelines

All network traffic must travel through the client interfaces defined in `@sodars/api`.

---

## 1. Flow
1. **Components** trigger hooks from `@sodars/api`.
2. **Hooks** trigger TanStack Query fetch actions.
3. **TanStack Query** calls generated client repositories.
4. **API Repositories** send network payloads via a unified Axios client containing global auth and tenant ID headers.

## 2. Configuration
The API client dynamically injects:
* `Authorization`: `Bearer <token>` headers.
* `X-Organization-Id`: Currently selected tenant identifier.
