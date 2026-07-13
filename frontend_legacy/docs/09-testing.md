# Testing Guidelines

We enforce rigorous automated testing on the frontend using Vitest and Playwright.

---

## 1. Unit & Integration Testing
* Run Vitest for components and hooks logic verification.
* Utilize `@sodars/testing` mock client server (MSW) to simulate API endpoint responses.
* Render tests must leverage `@testing-library/react`.

## 2. End-to-End Testing
* Run Playwright tests for cross-portal navigation and complete operational workflows (e.g., dispatch start to telemetry progress completion).
* Playwright scripts execute against local staging builds to verify complete UI interactions.
