# Coding Standards

This document establishes code standards to ensure consistency.

---

## 1. TS Conventions
* Enable strict type checking in `tsconfig.json`.
* Do not declare `any`. Write explicit interfaces and type parameters.
* Use `readonly` parameters for store states to prevent mutations outside actions.

## 2. Component Guidelines
* Write functional components with explicit React properties definitions.
* Standardize styling using Tailwind CSS utility tokens. Do not write inline styles.
* Ensure accessibility attributes (like `aria-label`, correct heading flows) are defined.
