# README INTEGRATION - Nutri Nova

## Installation rapide
1. Importer `sql/gestion_sport.sql`.
2. Importer ensuite `integration/SQL_MIGRATIONS/001_branding_updates.sql`.
3. (Optionnel) Importer `integration/SQL_MIGRATIONS/002_optional_seed_nutrinova.sql`.
4. Verifier `app/config/config.php` (host/user/password DB).
5. Ouvrir `http://localhost/azzzzzzayz/public/index.php?route=user/login`.

## Logo
- Placer le logo principal dans `public/assets/images/nutri-nova-logo.png` si vous avez la version PNG.
- L'application inclut deja un fallback SVG (`nutri-nova-logo.svg`) + fallback texte.

## Routes de test
- `index.php?route=user/register`
- `index.php?route=user/login`
- `index.php?route=dashboard/index`
- `index.php?route=programme/index`
- `index.php?route=coach/index`
- `index.php?route=seance/start&programme_id=1`
- `index.php?route=seance/history`
- `index.php?route=programme/exportPdf&id=1`
