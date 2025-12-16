# Security Guidelines for LLTool PHP

## Implemented Security Features

### 1. Authentication & Password Security
- Argon2id hashing with strong parameters (memory_cost, time_cost, threads)
- Password validation: 8+ chars, uppercase, lowercase, digits
- Rate limiting on login (5 attempts per 300 seconds per IP+email)
- Session regeneration after login
- Session timeout after 30 minutes of inactivity

### 2. Input Handling
- All user input sanitized with `sanitize_string()` and `sanitize_email()`
- Prepared statements used for all database queries
- Input length validation (max 255 chars for names)
- XSS attack pattern detection on all requests

### 3. CSRF Protection
- Token generation and verification on all forms
- Secure cookie-based tokens (httponly, secure flags)

### 4. Session Security
- HttpOnly cookies (prevents JS access)
- Secure flag (HTTPS only in production)
- SameSite=Lax to prevent cross-site request forgery
- Strict mode enabled
- 30-minute idle timeout

### 5. HTTP Security Headers
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY (prevents clickjacking)
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy: strict default-src 'self'
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: blocks geolocation, microphone, camera

### 6. Logging & Audit Trail
- All user actions logged: logins, CRUD operations, deletions
- Security events logged separately (failed logins, rate limit hits, XSS attempts)
- Logs stored in `logs/app.log` with timestamps and context

### 7. Error Handling
- Database errors do not expose sensitive information
- Generic error messages shown to users
- Full error details logged for debugging

### 8. Environment Management
- Separate `.env.example` and `.env.production` files
- Sensitive credentials not committed to version control
- Database credentials never hardcoded

## Setup for Production

1. **Database**
   - Use MySQL 5.7+ with InnoDB engine
   - Create a dedicated user with minimal privileges
   - Store credentials in `.env` (not `.env.example`)

2. **HTTPS**
   - Enable HTTPS (set SECURE_COOKIES=true in .env)
   - Use a valid SSL/TLS certificate
   - Redirect all HTTP traffic to HTTPS

3. **Secrets**
   - Use environment variables, never hardcode secrets
   - Rotate passwords regularly
   - Never commit `.env` files to git

4. **Monitoring**
   - Monitor `logs/app.log` for security events
   - Alert on repeated failed login attempts
   - Review audit trail regularly

5. **Database Backups**
   - Daily encrypted backups
   - Test backup restoration periodically

## Common Vulnerabilities Fixed

- **SQL Injection**: Prepared statements + parameterized queries
- **XSS**: Input sanitization + Content-Security-Policy header
- **CSRF**: Token-based protection on all state-changing operations
- **Brute Force**: Rate limiting on authentication
- **Session Hijacking**: Regeneration + httponly/secure flags
- **Info Disclosure**: Generic error messages + hidden internals
- **Clickjacking**: X-Frame-Options DENY header

## Testing Security

Run the migration and seed scripts to initialize the database:

```bash
php scripts/migrate.php
php scripts/seed.php
```

Default credentials (change immediately):
- Email: admin@example.com
- Password: password123 (meets strength requirements)

## Future Enhancements

- Two-factor authentication (2FA)
- API key authentication for machine-to-machine
- OAuth 2.0 integration (Google, GitHub)
- WAF (Web Application Firewall) rules
- Database encryption at rest
- Log aggregation to centralized SIEM
