# Fah Sai Place Project Instructions

- [x] Verify that this project instruction file exists.
- [x] Clarify requirements: PHP 8.2, MySQL/XAMPP, four roles, light-blue minimal responsive design, and nine Chapter 3 entities plus approved invoice/fine tables.
- [x] Scaffold and customize the application.
- [x] Install required extensions: none specified.
- [x] Validate PHP syntax, frontend JavaScript, responsive views, and SQL table count.
- [x] Create and run a task: not needed for this PHP application.
- [x] Launch the project with PHP's local development server.
- [x] Complete README and XAMPP setup instructions.

## Project Conventions

- Use PHP 8.2, PDO prepared statements, MySQL 8+, HTML, CSS, and vanilla JavaScript.
- Import `database/schema.sql` using phpMyAdmin. Preserve the nine Chapter 3 entities (`User`, `Room`, `Contract`, `Payment`, `MeterRecord`, `Repair`, `RepairDetail`, `CostType`, and `News`) and the approved `Invoice` and `Fine` extensions. `User.profileImage` and `News.image` store optional uploaded-file names; they do not add tables.
- Existing databases need the one-time schema update in `database/migrations/20261030_billing.sql`; do not run it twice.
- `Invoice` stores monthly rent and utility snapshots; `Fine` stores tenant fines; `Payment.invoiceId`/`fineId` link submissions to exactly one billing item in application workflows.
- The `Repair` table includes assignment, repair-result, and completion-photo fields to cover the stated workflow.
- Do not store plaintext passwords. Use `password_hash()` and `password_verify()`.
- Protect all state-changing requests with CSRF tokens and validate roles and record ownership on the server.
- Store uploaded evidence outside executable paths, validate MIME type and size, and serve it through the authorized `file.php` endpoint.
- Keep Thai interface copy and use the established pale-blue visual system in `assets/css/app.css`.
- Keep edits scoped, preserve existing project data, and run PHP lint after PHP changes.

## Data Model Note

The report's scope includes monthly invoices and fines, while its original Data Dictionary omits those entities. The user approved extending the database with `Invoice` and `Fine`; update the report's ERD/Class Diagram to match the current schema.

## Run Locally

Place the project under XAMPP `htdocs`, start Apache and MySQL, import `database/schema.sql` in phpMyAdmin, then open `http://127.0.0.1/fasai/`. Configure `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` for the selected database.
