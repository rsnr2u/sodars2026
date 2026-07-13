# SODAARS Development Principles

This document outlines the strict guidelines governing all SODAARS development lifecycle phases.

---

## 1. UI First
Backend is never developed before the corresponding UI layout, responsive interfaces, and interactive flows are approved by the user.

## 2. Workspace First
Every business feature must belong to a Workspace aggregate layout. Avoid isolated, disconnected pages.

## 3. Pattern First
Page and Workspace views are prohibited from direct layout components instantiation. Pages may only consume templates or patterns.

## 4. Mock First
Every screen must work completely using local mock data and factories seeds before integration checks.

## 5. Design System First
Tailwind raw colors or hardcoded spacing classes are banned inside business modules. Use Design System theme tokens.

## 6. Registry First
No hardcoded sidebar navigation, topbar profile links, or router path definitions are allowed. All layouts consume registries.

## 7. Plugin First
Features, dashboard widgets, and user settings must be extensible through modular registries.

## 8. Zero Duplication
If a component or sub-layout element is used more than once, it must be refactored and moved to `@sodars/design-system`.

## 9. Enterprise Ready
Every visual component (specifically the DataGrid) should be designed to support at least 100,000 records and multi-tenant scoping natively.

## 10. UX Approval Gate
No API integration, REST client requests, or backend logic begins until the corresponding UI is fully approved.
