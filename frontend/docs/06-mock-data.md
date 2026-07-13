# SODAARS Mock Data Strategy

## 1. Local Mock Generation
We generate rich, production-like Indian datasets under the `@sodars/mock-data` package.

### Generators include:
- **Providers**: Indian corporate legal names, state-wise cities, mobile numbers matching the `+91` prefix, and valid state-coded GST numbers.
- **Branches**: State-based partner branch names, regional offices, and coordinate mappings.
- **Staff**: Local team rosters, staff roles, and contact emails.
- **Inventory**: Digital and physical advertising hoarding sizes, media type codes, and availability statuses.
- **Campaigns**: Advertising budgets, run dates, and targeting criteria.

## 2. Usage Rules
- Avoid standard simple strings (e.g. "Test Provider", "123 Main St").
- Generated mock data must resemble live production transactions to ensure visual layouts and word wrapping match realistic enterprise workloads.
