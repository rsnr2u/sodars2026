# OpenAPI Specifications

We generate DTO definitions and HTTP service request methods directly from the Laravel backend.

---

## 1. Flow
1. Run backend command `php artisan l5-swagger:generate` to output the `openapi.json` file.
2. In the frontend root, run:
   ```bash
   npm run api:generate
   ```
3. The generator compiles type definitions inside `@sodars/contracts` and repository models inside `@sodars/api`.

## 2. Benefits
* Ensures absolute type safety across both frontend and backend teams.
* Cuts down manual typescript interface mapping hours.
