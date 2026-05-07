# ROUTES MAP

## Public
- `index.php?route=user/register` (GET/POST)
- `index.php?route=user/login` (GET/POST)
- `index.php?route=user/logout` (GET)

## Authentifie
- `index.php?route=dashboard/index` (admin/utilisateur)
- `index.php?route=seance/start&programme_id={id}` (admin/utilisateur)
- `index.php?route=seance/history` (admin/utilisateur)
- `index.php?route=seance/finish` (POST)
- `index.php?route=programme/exportPdf&id={id}` (admin/utilisateur)

## Admin
- `index.php?route=programme/index` (GET)
- `index.php?route=programme/store` (POST)
- `index.php?route=programme/update` (POST)
- `index.php?route=programme/delete&id={id}` (GET)
- `index.php?route=coach/index` (GET)
- `index.php?route=coach/store` (POST)
- `index.php?route=coach/update` (POST)
- `index.php?route=coach/delete&id={id}` (GET)

## Legacy compatible
- `index.php?action=login|register|logout|dashboard|programmes|coachs`
