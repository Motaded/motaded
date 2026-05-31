## Local setup (DDEV, Drupal 11)

1. Install and start DDEV.
2. From project root run `ddev start`.
3. Install dependencies: `ddev composer install`.
4. Import database: `ddev import-db --file=<dumpfile.sql.gz>`.
5. Rebuild caches: `ddev drush cr`.
6. Open site: `ddev launch`.

## CRM lead webhook (Contact us + setup cost calculator)

After a successful Webform submission, Drupal POSTs a JSON lead to your CRM when enabled.

**Admin UI:** Configuration → System → **CRM lead webhook** (`/admin/config/services/motaded-crm-webhook`)

- **Contact us** webform (`contact`) → `/contact-us`
- **Setup cost calculator** lead webform (`setup_cost_estimate_lead`)

This config is ignored by config sync (per-environment credentials). Optional override in `settings.local.php` via `$settings['motaded_crm_webhook']` (merged on top of admin values).

**Webhook URL on local dev** (CRM API on your machine, port 8000):

| Drupal runs on | Use in admin form |
|----------------|-------------------|
| Same host (MAMP, Valet, native PHP, no Docker) | `http://127.0.0.1:8000/api/webhooks/leads` or `http://localhost:8000/...` |
| DDEV / Docker container | `http://host.docker.internal:8000/api/webhooks/leads` |

Inside a container, `127.0.0.1` points at the container itself, not your Mac — that is why DDEV needs `host.docker.internal`.

UTM parameters (`utm_source`, `utm_medium`, `utm_campaign`, …) are stored in the visitor session on first hit and attached to the lead payload when the form is submitted.

Failed CRM requests are logged to the `motaded_custom` channel; the webform submission is still saved and email handlers still run.

## Project structure

- Composer root: project root (`composer.json`).
- Drupal docroot: `web/`.
- Config sync directory: `config/sync`.
- DDEV config: `.ddev/config.yaml` (`docroot: web`).