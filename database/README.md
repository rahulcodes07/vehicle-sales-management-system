# VSMS database workspace

The database is the source of truth for vehicle availability, sales integrity,
reporting, and audit history.

## Installation

1. Import `install.sql` into MySQL 8.0+.
2. Run `database/seed_demo.sql` to add the extended Indian dealership dataset.
3. Run `database/patches/001_concurrency_safe_sale.sql` on an existing database
   to update the sale procedure without resetting data.

The `install.sql` file is a complete clean-install script for XAMPP and
academic demonstrations. The supporting files make upgrades and demo data
easier to explain, test, and maintain separately.

## Database-first responsibilities

- **Tables and constraints** protect relationships and valid values.
- **Views** provide reusable read models for dashboard and reports.
- **Triggers** update inventory and create an immutable sales audit trail.
- **Stored procedures** own transactional sale behavior.
- **Stored functions** keep reusable server-side calculations close to data.
- **Indexes** support the most common inventory, customer, branch, and date lookups.
