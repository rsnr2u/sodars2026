# Coding Standards

This document defines coding conventions to keep the codebase clean.

---

## 1. Quality Rules
* Declare `strict_types=1` on every PHP file.
* Always define return types on methods.
* Keep controllers thin; delegate orchestration to lifecycle services.
* Preserve all comments and docstrings.

## 2. Abstractions
* Depend on interfaces rather than concrete implementations for external resources (e.g. `WebhookTransport`, `SearchProvider`).
* Bind singletons inside service providers (`NewModuleServiceProvider`).
