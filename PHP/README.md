# LLTool - PHP Versie

Volledige PHP implementatie van LLTool met Auth0 authenticatie en Sentry error tracking.

## Vereisten

- PHP 8.2 of hoger
- MySQL 5.7+ of MariaDB 10.3+
- Composer (voor dependencies)
- Extensions: PDO, cURL, OpenSSL, JSON, GD (voor foto's)

## Installatie op WebReus

### Stap 1: Dependencies installeren

**Optie A: Lokaal voorbereiden (Aanbevolen)**

1. Open een terminal/command prompt
2. Ga naar de `PHP/` map:
   ```bash
   cd PHP
   ```
3. Installeer dependencies:
   ```bash
   composer install --no-dev
   ```
   Dit maakt een `vendor/` map aan met alle benodigde libraries.
4. Upload de `vendor/` map naar je server

**Optie B: Via SSH op server**

Als je SSH toegang hebt tot je server:
```bash
cd /path/to/your/website
composer install --no-dev
```

### Stap 2: Upload naar WebReus

1. Upload **alle** bestanden uit de `PHP/` map naar je WebReus hosting
   - Gebruik FTP, SFTP of het WebReus file manager
   - Upload naar de **root** van je website (waar normaal `index.html` staat)
   
2. Zorg dat de volledige mapstructuur behouden blijft:
   ```
   / (root van je website)
   ├── index.php          ← Entry point
   ├── Bootstrap.php
   ├── composer.json
   ├── .htaccess          ← Apache configuratie
   ├── .env               ← Configuratie (maak aan van .env.example)
   ├── database.sql       ← Database schema
   ├── app/               ← Application code
   ├── storage/           ← File storage (moet schrijfbaar zijn)
   │   └── photos/        ← Student foto's
   ├── config/            ← Configuratie bestanden
   └── vendor/            ← Composer dependencies (belangrijk!)
   ```

### Stap 3: Maak directories aan

Zorg dat deze directories bestaan en schrijfbaar zijn:
- `storage/`
- `storage/photos/`
- `config/`

**Tip**: In WebReus file manager kun je meestal rechten instellen via "Properties" of "Permissions"
- Stel in op `775` of `777` (afhankelijk van je server configuratie)

### Stap 4: Database aanmaken

1. Maak een nieuwe MySQL database aan via je WebReus control panel
2. Importeer het `database.sql` bestand:
   - Via phpMyAdmin: Selecteer je database → Import → Kies `database.sql`
   - Via SSH: `mysql -u username -p database_name < database.sql`

### Stap 5: Configureer .env

1. Kopieer `.env.example` naar `.env`:
   ```bash
   cp .env.example .env
   ```

2. Open `.env` en vul de waarden in:

   ```env
   # Application
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://jouw-domein.nl

   # Database Configuration
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=je_database_naam
   DB_USERNAME=je_database_gebruiker
   DB_PASSWORD=je_database_wachtwoord

   # Auth0 Configuration
   # Haal deze waarden uit je Auth0 Dashboard: https://manage.auth0.com/
   AUTH0_DOMAIN=je-tenant.auth0.com
   AUTH0_CLIENT_ID=je_client_id
   AUTH0_CLIENT_SECRET=je_client_secret
   AUTH0_AUDIENCE=je_api_identifier
   AUTH0_CALLBACK_URL=https://jouw-domein.nl/auth/callback

   # Sentry Configuration (Optioneel)
   # Haal je DSN uit: https://sentry.io/settings/projects/
   SENTRY_DSN=
   SENTRY_ENVIRONMENT=production
   ```

### Stap 6: Configureer Auth0

1. Log in op [Auth0 Dashboard](https://manage.auth0.com)
2. Maak een nieuwe Application aan (Regular Web Application)
3. Voeg de Callback URL toe: `https://jouw-domein.nl/auth/callback`
4. Kopieer de Domain, Client ID, Client Secret en Audience naar je `.env` bestand

### Stap 7: Configureer Sentry (optioneel)

1. Log in op [Sentry.io](https://sentry.io)
2. Maak een nieuw project aan (PHP)
3. Kopieer de DSN naar je `.env` bestand

### Stap 8: Test de applicatie

1. Ga naar je website in de browser:
   - `https://jouw-domein.nl/index.php` 
   - Of gewoon: `https://jouw-domein.nl/` (werkt ook door .htaccess)
   
2. Je wordt doorgestuurd naar Auth0 login
3. Log in met je Auth0 account
4. Maak je eerste cohort aan
5. Voeg studenten toe aan het cohort

## Lokale Installatie (Development)

### Stap 1: Clone repository

```bash
git clone <repository-url>
cd lltool/PHP
```

### Stap 2: Installeer dependencies

```bash
composer install
```

### Stap 3: Configureer environment

Kopieer `.env.example` naar `.env` en vul de waarden in:

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=lltool
DB_USERNAME=root
DB_PASSWORD=

AUTH0_DOMAIN=your-tenant.auth0.com
AUTH0_CLIENT_ID=your_client_id
AUTH0_CLIENT_SECRET=your_client_secret
AUTH0_AUDIENCE=your_api_identifier
AUTH0_CALLBACK_URL=http://localhost:8000/auth/callback

SENTRY_DSN=
SENTRY_ENVIRONMENT=development
```

### Stap 4: Database aanmaken

```bash
# Maak database aan
mysql -u root -p -e "CREATE DATABASE lltool;"

# Importeer schema
mysql -u root -p lltool < database.sql
```

### Stap 5: Start development server

```bash
php -S localhost:8000 -t .
```

### Stap 6: Open browser

Ga naar `http://localhost:8000` en log in met Auth0.

## Database Structuur

De applicatie gebruikt de volgende tabellen:

- `cohorts` - Cohort/klas groepen
- `cohort_access` - Gedeelde toegang tot cohorts
- `students` - Studenten
- `settings` - Applicatie instellingen (optioneel, voor toekomstige features)
- `migrations` - Migratie tracking (optioneel)

## Directory Structuur

```
PHP/
├── app/
│   ├── Auth/              # Auth0 authenticatie
│   ├── Controllers/        # Application controllers
│   ├── Database/           # Database layer
│   ├── Error/             # Sentry error handling
│   ├── Middleware/         # Authentication middleware
│   ├── Models/             # Data models
│   ├── Services/           # Business logic
│   └── Session/            # Session management
├── migrations/             # Database migrations (optioneel)
├── views/                  # View templates
│   ├── cohorts/
│   ├── students/
│   └── layout.php
├── storage/               # File storage
│   └── photos/            # Student foto's
├── config/                 # Configuration files
├── index.php              # Entry point
├── Bootstrap.php          # Application bootstrap
├── database.sql           # Database schema (importeer dit!)
├── .env.example           # Environment template
└── composer.json          # Dependencies
```

## Gebruik

### Na installatie

1. Ga naar je website
2. Je wordt doorgestuurd naar Auth0 login
3. Log in met je Auth0 account
4. Maak je eerste cohort aan
5. Voeg studenten toe aan het cohort

## Troubleshooting

### Dependencies ontbreken

Als je een foutmelding krijgt over ontbrekende dependencies:
1. Zorg dat `vendor/` map aanwezig is
2. Run `composer install --no-dev` opnieuw
3. Upload de `vendor/` map opnieuw

### Database connectie fout

1. Controleer je `.env` bestand
2. Zorg dat database credentials correct zijn
3. Zorg dat database bestaat en toegankelijk is
4. Test connectie via phpMyAdmin of MySQL client

### Permissions fout

Als je fouten krijgt over schrijfrechten:
1. Zorg dat `storage/` en `config/` directories bestaan
2. Stel permissions in op `775` of `777`:
   ```bash
   chmod -R 775 storage config
   ```

### Auth0 werkt niet

1. Controleer je `.env` bestand
2. Zorg dat Callback URL correct is ingesteld in Auth0 Dashboard
3. Controleer dat alle Auth0 credentials correct zijn

---
