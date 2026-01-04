# Fix Autoloader Probleem

## Probleem
De autoloader kan de Bootstrap class niet vinden omdat de autoloader cache niet up-to-date is.

## Oplossing

Voer in de Docker container uit:

```bash
composer dump-autoload
```

Of als je composer.phar gebruikt:

```bash
php composer.phar dump-autoload
```

Dit genereert de autoloader opnieuw met de juiste namespace mappings.

## Na dump-autoload

Refresh de pagina in je browser. De Bootstrap class zou nu gevonden moeten worden.

