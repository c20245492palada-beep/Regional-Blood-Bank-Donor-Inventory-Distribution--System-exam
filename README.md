# BSIS-3B Blood Bank Laravel Project

This is a complete Laravel 13 application with the blood-bank assignment implemented in the standard Laravel project structure.

## Requirements

- PHP 8.3 or later
- Composer 2
- SQLite (included with the configured XAMPP PHP installation), or another database supported by Laravel

## Run locally on Windows

The project already has its dependencies installed and its SQLite database migrated. In PowerShell, from this project folder, start the development server:

```powershell
& "C:\xampp\php\php.exe" artisan serve
```

Open `http://127.0.0.1:8000`. The API health endpoint is `http://127.0.0.1:8000/up`.

To run migrations or tests, use `& "C:\xampp\php\php.exe" artisan migrate` or `& "C:\xampp\php\php.exe" artisan test`. The local `.env` is configured for SQLite. For a fresh checkout, install PHP 8.3+ and Composer 2, run `composer install`, copy `.env.example` to `.env`, generate the key with `php artisan key:generate`, create `database/database.sqlite`, then run `php artisan migrate`.

## Blood-bank schema (3NF)

The raw row combines donor, donation, hospital request, and distribution facts. Donor attributes depend on the donor; hospital names depend on the hospital; and issue details belong to a specific issued bag and request. The domain is decomposed into five normalized tables:

1. `donors`: unique national ID, name, and blood group.
2. `hospitals`: hospital name.
3. `blood_bags`: donor, bag code, donation date, and availability.
4. `hospital_requests`: hospital, requested blood group, date, units, and request status.
5. `distribution_logs`: issued bag, hospital request, units, fee, status, and issue time.

Each table stores attributes about its own key. Donor and hospital details are referenced rather than repeated. A unique constraint on `distribution_logs.blood_bag_id` prevents a bag from being issued twice. The other three migrations are Laravel framework support tables.

## Architecture

- `app/Models`: Eloquent models and relationships.
- `app/Http/Requests`: dedicated store/update validation requests.
- `app/Http/Controllers`: JSON resource actions for blood bags and distribution logs.
- `app/Services/BloodBankService.php`: sanitization, blood-group compatibility, and transactional distribution rules.
- `database/migrations`: framework tables and the five domain tables.
- `routes/api.php`: API endpoints, enabled through `bootstrap/app.php`.

## Business rules

- New bags are `Available`; only available bags can be edited.
- Distribution locks the bag and request in a transaction, checks availability and compatibility, enforces the remaining requested units, creates an issue log, marks the bag `Issued`, and updates request status.
- ABO/Rh red-cell compatibility: O can donate across ABO types; A to A/AB; B to B/AB; AB to AB. Rh-negative donors can donate to either Rh type; Rh-positive donors only to Rh-positive recipients.
- Blood group values are normalized and inconsistent embedded/separate Rh signs are rejected. Donor IDs are uppercased and stripped of punctuation and whitespace. Fees are stored to two decimal places.
- Distribution-log updates are limited to correcting the processing fee.

## API endpoints

| Method | Endpoint | Action |
|---|---|---|
| GET | `/api/blood-bags` | Paginated bags with donors and distribution details |
| GET | `/api/blood-bags/create` | Donor choices |
| POST | `/api/blood-bags` | Register a bag |
| PUT/PATCH | `/api/blood-bags/{bloodBag}` | Update an available bag |
| GET | `/api/distribution-logs` | Paginated logs with bag, donor, request, and hospital |
| GET | `/api/distribution-logs/create` | Available bags and open requests |
| POST | `/api/distribution-logs` | Validate and distribute a bag |
| PUT/PATCH | `/api/distribution-logs/{distributionLog}` | Correct the processing fee |

The example Form Requests currently authorize all callers. Add authentication and policies before exposing these endpoints in production.
