# Docker Installatie Instructies

## Dependencies installeren in Docker container

### Stap 1: Start de containers

```bash
docker-compose up -d
```

### Stap 2: Ga de PHP container in

```bash
docker-compose exec web bash
```

Of als je de container naam gebruikt:
```bash
docker exec -it lltool-web bash
```

### Stap 3: Installeer dependencies

In de container, voer uit:

```bash
cd /var/www/html
composer update --no-interaction
```

Of als composer niet beschikbaar is:

```bash
php composer.phar update --no-interaction
```

### Stap 4: Controleer installatie

```bash
ls -la vendor/
```

Je zou de `vendor/` directory moeten zien met alle dependencies.

## Snelle Commando's

**Alles in één keer:**
```bash
docker-compose exec web composer update --no-interaction
```

**Of met composer.phar:**
```bash
docker-compose exec web php composer.phar update --no-interaction
```

## Troubleshooting

**Als zip extension ontbreekt:**
```bash
docker-compose exec web docker-php-ext-install zip
```

**Als GD extension ontbreekt:**
```bash
docker-compose exec web docker-php-ext-install gd
```

**Rebuild container na Dockerfile wijzigingen:**
```bash
docker-compose build --no-cache
docker-compose up -d
```

