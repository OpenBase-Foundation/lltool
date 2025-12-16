LLTool — PHP scaffold

This folder contains a minimal PHP rewrite scaffold of the original React project with comprehensive security, setup wizard, and optional user registration.

## Quick Start

### SQLite (Local Development)

```powershell
cd 'c:\Users\mats\OneDrive - Nuovo\1. websites\lltool\PHP'
New-Item -ItemType File -Path database\database.sqlite
php scripts/migrate.php
cd public
php -S localhost:8000
```

Then visit http://localhost:8000 and follow the setup wizard.

### MySQL (Production)

1. Start MySQL and phpMyAdmin:
```powershell
cd PHP
docker-compose up -d
```

2. Create or let the setup wizard create a `.env` with MySQL credentials:

Option A — let the setup wizard handle it (recommended):

- Start the app (see Step 4). On first run the setup wizard will ask for database credentials and will create a `.env` file for you. This is the simplest path for new deployments.

Option B — create the `.env` file manually before running migrations:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=lltool
DB_USER=lltool_user
DB_PASS=lltool_pass
```

3. Apply migrations:
```powershell
cd PHP
php scripts/migrate.php
```

4. Start the server:
```powershell
cd PHP\public
php -S localhost:8000
```

## Setup Wizard

On first visit, you'll see a setup page where you can configure:
- **Organization name** — Your school/company name
- **Admin account** — Create the initial admin user
- **User registration** — Allow users to self-register or admin-only

The setup page is locked after first run and cannot be accessed again.

## Features

- **Security Hardened** — See `SECURITY.md` for details (rate limiting, CSRF, XSS protection, etc.)
- **Setup Wizard** — Guided configuration on first deployment
- **User Registration** — Optional self-registration (configurable in setup)
- **Audit Logging** — All actions and security events logged
- **MySQL & SQLite** — Works with both databases
- **Docker Compose** — Ready-to-run MySQL stack

## After Setup

Once initialized, log in with the admin credentials you created during setup and start managing cohorts and students.
\nUpdated README to describe React and PHP projects
\nUpdated README to describe React and PHP projects
