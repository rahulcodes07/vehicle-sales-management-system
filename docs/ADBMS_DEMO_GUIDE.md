# ADBMS demonstration guide

Use the application in this order during a project presentation:

1. **Dashboard** — show aggregate `COUNT`, `SUM`, `COALESCE`, and the
   `view_inventory_summary` / `view_employee_performance` views.
2. **Inventory** — filter by type and status; explain the `idx_vehicle_status`
   index and the `view_available_vehicles` view.
3. **Record Sale** — select an available vehicle and submit a transaction.
   Explain that PHP calls the proposal procedure `AddSale`, which delegates to
   the locking transaction procedure `sp_RecordSale`.
4. **Inventory and Audit** — show that the vehicle becomes `Sold` and that
   `trg_after_sale_insert` created an audit row automatically.
5. **Cancel Sale** — submit the protected cancellation form and demonstrate
   `trg_after_sale_delete` restoring inventory.
6. **Analytics & Views** — choose a year for
   `sp_GetMonthlySalesReport`, review employee performance, and run
   `GetTotalSalesByEmployee()` and `fn_CalculateDiscount`.
7. **Constraints** — attempt a duplicate customer phone, invalid insurance
   dates, or deletion of a referenced vehicle to demonstrate database
   enforcement.

## Phase 3 traceability

| Proposal deliverable | MySQL object | Application demonstration |
|---|---|---|
| Multi-table join | `Sales_Summary` | Sales/dashboard summary |
| Nested query: repeat buyers | Nested `IN` subquery | Analytics page |
| Nested query: above-average sales | Scalar `AVG()` subquery | Analytics page |
| Query optimization | `EXPLAIN` | Live execution-plan table |
| Employee sales function | `GetTotalSalesByEmployee()` | Employee analytics table |
| Sale procedure | `AddSale()` → `sp_RecordSale()` | Record Sale page |

The UI labels the database object used by each page so the application and
database behavior can be explained together rather than as separate topics.
