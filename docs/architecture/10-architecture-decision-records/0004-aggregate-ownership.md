# ADR 0004: Bounded Context Aggregate Ownership

## Context
When multiple modules mutate or share resource states without clear boundaries, domain models become coupled and circular dependency errors occur during lifecycle orchestration.

## Decision
We enforce a strict Aggregate Root ownership matrix:

| Bounded Context | Aggregate Root | Child Entities & History Models |
| --- | --- | --- |
| **CRM** | `Lead` / `Opportunity` | `Quotation`, `QuotationVersion` |
| **Campaigns** | `Campaign` | `Creative`, `Proof`, `Schedule` |
| **Bookings** | `Booking` | `BookingActivity` |
| **Wallet** | `Wallet` | `Transaction`, `Withdrawal`, `Settlement` |
| **Transport** | `Vehicle` / `Driver` / `Route` | `Maintenance`, `Fuel`, `GPSLog` |
| **Operations** | `Schedule` / `ScheduleExecution` | `Assignment`, `Checkpoint`, `Conflict`, `Timeline` |
| **Providers** | `Provider` | `Document`, `Staff`, `BankAccount` |

All modifications to child entities must flow through the parent context's Aggregate Root.

## Consequences
* Hermetic boundaries between platforms.
* Simple transaction locks and clear ownership mapping.
