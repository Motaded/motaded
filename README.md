## Local setup (DDEV, Drupal 11)

1. Install and start DDEV.
2. From project root run `ddev start`.
3. Install dependencies: `ddev composer install`.
4. Import database: `ddev import-db --file=<dumpfile.sql.gz>`.
5. Rebuild caches: `ddev drush cr`.
6. Open site: `ddev launch`.

## Project structure

- Composer root: project root (`composer.json`).
- Drupal docroot: `web/`.
- Config sync directory: `config/sync`.
- DDEV config: `.ddev/config.yaml` (`docroot: web`).