# 11. Coding Standards

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define standard code patterns, styling rules, and API design patterns. This ensures code written by human programmers and AI agents remains uniform and clean.

---

## Scope

This document covers:
* Laravel backend coding rules (strict typing, PSR-12, Service-Controller separation).
* React web and React Native coding rules (functional components, CSS variables, folder layouts).
* REST API request/response specifications and error handling models.

---

## Business Rules

### 1. Backend Standards (Laravel & PHP)
* **Strict Typing**: Every PHP file must declare strict typing at the very first line:
  ```php
  <?php
  declare(strict_types=1);
  ```
* **Coding Style**: Follow PSR-12 coding formatting.
* **Separation of Concerns**:
  * **Controllers**: Must remain thin. Their only roles are HTTP request extraction, routing to a service, validation schema mapping, and returning standard JSON API resources.
  * **Services**: All business logic (pricing calculators, S3 storage updates, booking scheduler logic) must live in discrete PHP Service classes inside `app/Services/`.
  * **Models**: Use Eloquent models for database operations. Avoid writing raw SQL queries.
* **Database Migrations**: Always define explicit column lengths, foreign key constraints (e.g., `restrictOnDelete()`), and table descriptions/comments where applicable.

### 2. Frontend Standards (React / React Native)
* **Component Structures**: Write components using React Functional Components with React Hooks. Do not write class components.
* **Styling (Vanilla CSS)**:
  * Do not install TailwindCSS unless specifically authorized.
  * Use a global `index.css` file containing CSS variables for colors, typography, margins, and transitions (e.g., `--color-primary-base`, `--font-family-sans`).
  * Follow the BEM (Block-Element-Modifier) methodology for naming CSS selectors (e.g., `booking-card__price-tag--discounted`).
* **API Hooks**: Wrap all HTTP calls inside custom React hooks or service functions (e.g., `useFetchInventory`, `bookingService.create()`) to decouple UI components from network calls.

### 3. REST API Design Standards
* **Endpoint Conventions**: Resource names must use plural lowercase nouns (e.g., `/api/v1/branches`, `/api/v1/digital-assets`).
* **HTTP Methods**: Use GET (retrieve), POST (create), PUT (update complete), PATCH (update partial), and DELETE (remove).
* **API Payload Blueprint**:
  * **Success Response (200 OK / 201 Created)**:
    ```json
    {
      "success": true,
      "data": {
        "id": "a69f72c1-d419-482a-a9a3-5c8e23b12345",
        "name": "Downtown LED Screen 01"
      }
    }
    ```
  * **Validation Error (422 Unprocessable Entity)**:
    ```json
    {
      "success": false,
      "message": "The given data was invalid.",
      "errors": {
        "net_price_cents": ["The net price cents field must be an integer."]
      }
    }
    ```

---

## Future Scope

* Lint checks inside pre-commit hooks (e.g., Husky, PHP CS Fixer) to automatically fail commits that do not meet these coding guidelines.
