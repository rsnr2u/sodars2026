# SODAARS Navigation System

## 1. Routing Model
We use a file-based routing model mapped under the `apps/admin/src/routes/` folder.
- `/` - Landing Dashboard
- `/providers` - Partners & Providers Directory
- `/inventory` - Physical and Digital Inventory Assets
- `/campaigns` - Media Campaigns
- `/bookings` - Client Bookings Pipeline
- `/customers` - CRM & Customers List
- `/finance` - Ledger and Settlement Accounts
- `/reports` - Analytics and Reports exports
- `/settings` - Workspace configuration

## 2. Shared Workspace Context
The sidebar and top navigation remain persistent across all modules. Selecting a tab triggers instant navigation.
The workspace views support Saved Views tabs at the top of directory listings for quick sub-filters.
