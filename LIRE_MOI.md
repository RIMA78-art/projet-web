# NutriNova — Guide d'installation

## 1. Copier le dossier

Placer `NUTRINOVA_MVC` dans `C:\xampp\htdocs\`

## 2. Migration base de données

Dans **phpMyAdmin** → sélectionner la base `nutrinova` → onglet **SQL** → coller et exécuter :

```sql
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS reset_token   VARCHAR(64) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS reset_expires DATETIME    DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS google_id     VARCHAR(64) DEFAULT NULL;
```

## 3. Configuration email (Reset mot de passe)

Ouvrir `controleur/frontoffice/ResetControleur.php` et modifier :

```php
define('MAIL_FROM',     'votre.email@gmail.com');  // ← votre Gmail
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx');    // ← mot de passe app 16 chars
define('SITE_URL',      'http://localhost/NUTRINOVA_MVC');
```

**Obtenir le mot de passe d'application Gmail :**
1. Aller sur https://myaccount.google.com/apppasswords
2. Taper "NutriNova" dans le champ → cliquer Créer
3. Copier les 16 lettres affichées → les coller dans MAIL_PASSWORD

## 4. Configuration Google OAuth

Ouvrir `controleur/frontoffice/GoogleControleur.php` et modifier :

```php
define('GOOGLE_CLIENT_ID',     'VOTRE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'VOTRE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI',  'http://localhost/NUTRINOVA_MVC/controleur/frontoffice/GoogleControleur.php?action=callback');
```

**Obtenir les identifiants :**
1. https://console.cloud.google.com/
2. APIs & Services → Identifiants → Créer → ID client OAuth 2.0
3. Type : Application Web
4. URI de redirection : `http://localhost/NUTRINOVA_MVC/controleur/frontoffice/GoogleControleur.php?action=callback`

> ⚠️ Le bouton Google dans la page web appelle maintenant `redirectToGoogle()` en JS  
> qui calcule le chemin automatiquement — plus de problème de chemin relatif.

## 5. Nouvelles fonctionnalités

### 🌙 Mode sombre / clair
- Bouton 🌙/☀️ dans la barre de navigation
- La préférence est sauvegardée automatiquement (localStorage)

### 🌐 Traduction (FR / EN / AR)
- Bouton `FR` / `EN` / `AR` dans la barre de navigation (cycle au clic)
- Supporte le RTL pour l'arabe
- La préférence est sauvegardée automatiquement (localStorage)

## 6. Accès

- **Application** : http://localhost/NUTRINOVA_MVC/vue/frontoffice/user.html
- **Admin**       : http://localhost/NUTRINOVA_MVC/vue/backoffice/login.php
