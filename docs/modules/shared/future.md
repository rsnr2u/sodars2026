# Shared Module: Future Scope

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to list future requirements and business features for Shared that are out of scope for Version 1.

---

## Scope

This document specifies:
* Out-of-scope media processing scripts.
* Future search infrastructure upgrades.
* Global translations utilities.

---

## Business Rules

### 1. Deferred Features (Out of Scope for V1)

* **Cloudflare R2 & CDN Configurations**:
  * Integrating custom global Content Delivery Networks (CDN) and migrating from AWS S3 to Cloudflare R2 object storage to optimize asset delivery speeds.
* **Automated Video Processing & Transcoding**:
  * Compiling video formats automatically (generating multiple resolutions, HLS/DASH streams, and codecs checks) via AWS Elemental MediaConvert hooks.
* **AI Image Compression**:
  * AI algorithms optimizing image qualities and sizes before saving to S3.
* **OCR & Document Reading AI**:
  * Automatic scanning of uploaded provider tax files or corporate documents using Document AI to extract registration metadata.
* **Video/Image Privacy Editing**:
  * Automated tools (face blurring, logo masking, background removal) to edit site installation pictures before publishing to the public marketplace.
* **Vector Search Engines**:
  * Migrating database search indices to Elasticsearch or OpenSearch clusters to support semantic searches on maps.
* **Universal Translation Engine**:
  * Text translation wrappers translating user comments and notes dynamically in portals.

---

## Future Scope

* Re-evaluate these requirements during Version 2 scoping sessions.
