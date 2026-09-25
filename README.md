## Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

If asked to create the SQLite database, choose yes.

## Run

HTTP: open http://localhost:8000 and select or drop a CSV file. For all results, tick the checkbox before selecting the file.

CLI:

```bash
php artisan employees:calculate /path/to/file.csv
php artisan employees:calculate /path/to/file.csv --all
```

Both show the top 20 by default. For bigger files I recommend CLI, to avoid upload limits and loading too much data in the browser.

## Assumptions

- I calculate shared days per pair and project, not a sum across different projects.
- Both start and end dates count. Same-day overlap is 1 day. Weekends and holidays count too.
- `NULL` end date means today, in the app timezone (UTC). Empty dates are invalid.
- One employee can have multiple periods on a project. Overlapping periods are merged to avoid counting days twice.
- Invalid rows are skipped and counted. Blank lines are ignored.
- Supported dates: `YYYY-MM-DD`, `YYYY/MM/DD`, `DD-MM-YYYY`, `DD/MM/YYYY`, `DD.MM.YYYY`.
- The expected header is `EmpID,ProjectID,DateFrom,DateTo`, as in the assignment. Files without a header are also accepted.
- I assumed the calculation should happen at runtime, without saving employees and projects in a database. Laravel still uses database sessions by default, which is why migration is included.
- Temporary files are used to reduce memory usage and are cleaned up after processing. One project is still loaded in memory at a time, so a very large single project can need more memory.
