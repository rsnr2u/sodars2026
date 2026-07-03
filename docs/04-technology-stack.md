# 04. Technology Stack

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to establish the standardized technology stack for SODARS, ensuring consistency, rapid development, and smooth onboarding for all team developers and automated coding assistants.

---

## Scope

This document specifies the software languages, frameworks, utility libraries, hosting infrastructures, and database architectures selected for all modules in Version 1.

---

## Business Rules

### 1. Technology Choices

| Layer | Technology | Key Rationale |
| :--- | :--- | :--- |
| **Backend API Engine** | Laravel (PHP 8.2+) | Robust ecosystem, Eloquent ORM, secure auth (Sanctum), and native queuing for file uploads/notifications. |
| **Frontend Web Portals** | React + Vite / Next.js | Component-based reusability, quick bundle builds, and reactive UI states. |
| **Web Styling** | Vanilla CSS (CSS Variables) | Full flexibility, zero compile dependency overhead, high styling performance, and custom design-system tokens. |
| **Mobile Application** | React Native (Expo) | Single cross-platform codebase (iOS & Android) with rapid onboarding. |
| **Relational Database** | MySQL 8.0+ | ACID compliance for transaction safety, foreign key mapping for multi-branch assets, and native spatial support for geocoding. |
| **Maps Service** | Google Maps API | Industry-standard map visualization, auto-complete address fields, and marker cluster tracking. |
| **Object Storage** | AWS S3 / MinIO | Scalable storage for high-resolution images, PDF proofs, and MP4 video ad assets. |
| **Payment Gateway** | Stripe / Razorpay | Reliable webhook-based payment handling, sandbox testing, and simple checkout redirections. |

### 2. Stack Integration Standards
* **Decoupled Architecture**: The backend must run purely as a stateless REST API. Blade templates must not be used for client portals; all web clients communicate via JSON over HTTPS.
* **State Management**: For React web portals, use lightweight state management (e.g., Zustand or React Context) to avoid heavy Redux boilerplates.
* **API Communication**: Axios or Native Fetch with central interceptor logic for auto-attaching authorization headers and handling HTTP 401 unauthorized states.

---

## Future Scope

* Programmatic integrations using gRPC or WebSocket connections for real-time digital screen player synchronizations.
* Migration of media storage to high-bandwidth Content Delivery Networks (CDNs) such as Cloudflare or AWS CloudFront to reduce media delivery latency to physical screens.
