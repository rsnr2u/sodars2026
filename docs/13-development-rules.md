# 13. Development Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to establish the development "constitution" for SODARS. It defines coding restrictions and architectural boundaries that developers and AI agents must follow during implementation.

---

## Scope

This document covers:
* Code boundaries (No feature creep, V1 focus).
* Architectural mandates (Configuration-driven design, reusable REST APIs, branch isolation).
* Security rules (File uploads, input sanitization, database validation).

---

## Business Rules

### 1. Scope Governance (No Feature Creep)
* **V1 Priority**: Do not implement any speculative code or partial placeholders for V2 features (e.g., empty routes for "AI recommendations" or "Agency Accounts").
* **Keep It Simple**: Choose the simplest implementation that achieves the user flow. For example, if a screen goes offline, email the branch manager rather than trying to build automated regional backup routing logic in V1.

### 2. Architecture Mandates
* **Branch-First Isolation**:
  * Every model retrieval query (except for Super Admins) must be automatically scoped by the user's `branch_id`.
  * Hardcoding branch IDs is strictly prohibited.
* **Configuration-Driven Logic**:
  * The maximum markup percentage must be fetched from the database configuration settings table.
  * System values (supported image/video mime-types, file size upload boundaries) must be loaded from Laravel config files backed by `.env` options.
* **API Consumer Agnosticity**:
  * Do not design backend API endpoints tailored to only one specific layout configuration.
  * Ensure API response payloads return structured resources that can be rendered on both the React web client and the React Native mobile app seamlessly.

### 3. Critical Security Boundaries
* **Input Sanitization**: All incoming request attributes must undergo strict Laravel Validator validation.
* **Media Upload Guardrails**:
  * Accept only specified MIME types for ad creatives (e.g., `image/png`, `image/jpeg`, `video/mp4`).
  * Verify media file sizes before generating pre-signed S3 upload links (maximum file size limit of 50MB for videos).
* **Payment Validation**:
  * Never trust pricing parameters sent directly from the client during checkout.
  * The backend must recalculate the total order amount from screen net prices and current branch markup settings prior to initiating payment gateway sessions.

---

## Future Scope

* Regular review of development rules at the beginning of each major release cycle.
