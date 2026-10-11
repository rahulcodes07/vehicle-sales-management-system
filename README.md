# Vehicle Sales Management System (VSMS)

VSMS is a database-first Advanced Database Management System project for an
Indian vehicle dealership. PHP provides the web interface, while MySQL owns
the core business rules: referential integrity, inventory state transitions,
transaction processing, reporting views, stored functions, and audit triggers.

## Quick start on Windows / XAMPP

1. Create a local `.env` from `.env.example` and set the MySQL password.
2. Import `database/install.sql` into MySQL 8.0+.
3. Run `database/seed_demo.sql` for additional Indian dealership records.
4. If the database already exists, run
   `database/patches/001_concurrency_safe_sale.sql`.
5. Install the Phase 3 proposal objects:

   ```text
   mysql -u root -p vsms_db < database/patches/002_phase3_proposal_objects.sql
   ```

6. Verify the Phase 3 objects with
   `database/patches/003_phase3_verification.sql`.
7. Apply `database/patches/004_function_metadata.sql` for the corrected
   date-dependent discount function metadata.
8. Serve the repository through Apache/XAMPP or run:

   ```text
   php -S localhost:8000 -t public
   ```

9. Open `http://localhost:8000/index.php`.

## Application structure

```text
/
├── app/
│   ├── Config/       Environment-aware configuration
│   ├── Core/         Bootstrap, escaping, sessions, CSRF, UI helpers
│   └── Services/     Business workflows that delegate to MySQL
├── assets/
│   └── js/           Small progressive-enhancement interactions
├── database/
│   ├── install.sql   Complete clean-install database script
│   ├── patches/      Non-destructive upgrades for existing databases
│   ├── README.md
│   └── seed_demo.sql
├── public/           Only web-accessible files
│   ├── *.php         Browser entry points
│   └── assets/       CSS and JavaScript
└── docs/             ADBMS presentation and demonstration guides
```

The web server document root should be `public/`. Keeping application code,
configuration, database scripts, and documentation outside that directory
prevents accidental direct access to internal files. Shared logic is
progressively moved into `app/`, while each page remains easy to demonstrate
in an academic viva.

## ADBMS features demonstrated

| Feature | Implementation |
|---|---|
| Normalized relational model | Branch, Employee, Customer, Vehicle, Sales, Insurance |
| Constraints | Primary keys, foreign keys, unique values, checks, enums |
| Indexing | Status, sale date, customer phone, employee branch |
| Views | Inventory, sales details, employee performance, inventory summary |
| Triggers | Vehicle status transitions and immutable sales audit events |
| Stored procedures | ACID sale transaction and monthly sales report |
| Stored function | Manufacturing-year discount calculation |
| Transaction safety | Row locking, commit, rollback, and status output |

## Important workflow

Vehicle sales are intentionally written through `sp_RecordSale`, not through a
PHP fallback insert. MySQL locks the selected vehicle, verifies availability,
inserts the sale, lets the trigger mark the vehicle as sold, writes the audit
event, and commits the complete unit of work.
