# 🎯 Système de Scoring et Badges Utilisateurs

## 📋 Vue d'ensemble

Un système automatique qui calcule un **score utilisateur (0-100)** et attribue des badges de statut :
- 🔥 **Actif** (score ≥ 70) — Utilisateur très engagé
- ⚡ **Modéré** (40-69) — Utilisateur modérément engagé  
- 💤 **Inactif** (< 40) — Utilisateur peu engagé

---

## 📊 Logique de Scoring

Le score est calculé sur 4 critères :

### 1️⃣ Dernière Connexion (max 30 points)
- **< 2 jours** → +30 points
- **< 7 jours** → +20 points
- **Sinon** → +5 points
- **Jamais connecté** → +0 points

### 2️⃣ Complétude du Profil (max 20 points)
Évalue 7 champs obligatoires : nom, prénom, email, objectif, niveau_sport, taille, poids

- **Profil complet** (tous les champs) → +20 points
- **Profil partiel** (50% des champs) → +10 points
- **Profil vide** (< 50%) → +0 points

### 3️⃣ Mise à Jour du Poids (max 30 points)
- **< 7 jours** → +30 points (à jour récemment)
- **< 30 jours** → +15 points (à jour ce mois)
- **Aucune mise à jour** → +0 points

### 4️⃣ Ancienneté du Compte (max 20 points)
- **≥ 30 jours** → +20 points
- **< 30 jours** → +10 points (nouveau compte)

**Total max : 100 points**

---

## ⚙️ Installation

### Étape 1 : Migration SQL

Exécutez le script SQL suivant via phpMyAdmin ou MySQL CLI :

```sql
-- Ajouter les colonnes de suivi
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL;

ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS last_weight_update DATETIME DEFAULT NULL;

-- Initialiser les dates existantes
UPDATE utilisateur SET last_weight_update = date_inscription WHERE last_weight_update IS NULL;

-- Index pour optimiser les recherches
CREATE INDEX IF NOT EXISTS idx_last_login ON utilisateur(last_login);
CREATE INDEX IF NOT EXISTS idx_last_weight_update ON utilisateur(last_weight_update);
```

**Ou** exécutez le fichier migration fourni :
```bash
mysql -u root -p nutrinova < sql_migration_scoring.sql
```

### Étape 2 : Code PHP (déjà implémenté)

Les méthodes suivantes ont été ajoutées à `modele/User.php` :

- `User::calculerScore($userData)` — Calcule le score (0-100)
- `User::obtenirBadge($score)` — Retourne les données du badge (emoji, label, couleur)
- `User::obtenirAvecScoreBadge($id)` — Données utilisateur complètes avec score
- `User::mettreAJourDerniereConnexion($id_user)` — Enregistre la dernière connexion

### Étape 3 : Utilisation dans le Contrôleur

Le `controleur/backoffice/AdminControleur.php` retourne automatiquement le score et badge pour chaque utilisateur :

```php
// Réponse API
{
  "success": true,
  "users": [
    {
      "id_user": 1,
      "nom": "Dupont",
      "prenom": "Jean",
      // ... autres champs ...
      "score": 85,
      "badge": {
        "emoji": "🔥",
        "label": "Actif",
        "css_class": "badge-active",
        "description": "Utilisateur très engagé"
      }
    }
  ]
}
```

### Étape 4 : Affichage dans la Vue

La colonne **Statut** s'affiche automatiquement dans le tableau de gestion des utilisateurs avec :
- L'emoji du badge
- Le label du statut
- Le score /100
- Une couleur cohérente avec le statut

---

## 🔧 Intégrations

### Mettre à jour Last Login

À chaque authentification réussie (login classique ou Google) :

```php
// Dans votre contrôleur d'authentification
User::mettreAJourDerniereConnexion($userId);
```

### Mettre à jour Last Weight Update

La mise à jour est **automatique** quand le poids change via `User::mettreAJour()`.

---

## 📁 Fichiers Modifiés

1. **`modele/User.php`** — 120+ lignes ajoutées
   - Méthodes de scoring
   - Méthodes de badge
   - Mise à jour des dates de connexion/poids

2. **`controleur/backoffice/AdminControleur.php`**
   - Intégration du score/badge dans les routes `get-all` et `search`

3. **`vue/backoffice/user.html`**
   - Ajout colonne "Statut" dans le tableau
   - Changement colspan de 11 à 12

4. **`vue/backoffice/user1.js`**
   - Fonctions `renderBadge()`, `getBadgeColor()`
   - Mise à jour de `renderUsersTable()` pour afficher les badges
   - Ajout de styles conditionnels basés sur le statut

5. **`sql_migration_scoring.sql`** (nouveau fichier)
   - Script de migration pour les colonnes manquantes

---

## 🎨 Styles CSS (Tailwind)

Les badges utilisent les classes Tailwind :
- **Actif** (🔥) : `badge-active` → fond vert, texte vert
- **Modéré** (⚡) : `badge-moderate` → fond jaune, texte jaune
- **Inactif** (💤) : `badge-inactive` → fond gris, texte gris

Personnalisables via `getBadgeColor()` dans `user1.js`.

---

## ✅ Validation

Pour tester le système :

1. **Créez un nouvel utilisateur** → Score initial = 10 (nouveau compte)
2. **Modifiez le poids** → Score augmente de +30 (mise à jour récente)
3. **Complétez le profil** → Score augmente de +20 (profil complet)
4. **Attendez 2 jours** → Badge devient ⚡ (Modéré)
5. **Attendez 30 jours** → Badge devient 💤 (Inactif) + ancienneté +20

---

## 📌 Notes Importantes

- Les colonnes `last_login` et `last_weight_update` sont **NULL** pour les anciens utilisateurs jusqu'à leur prochaine action
- Le score est calculé **à la volée** (pas de stockage en BD)
- Les badges sont dérivés du score (pas de stockage en BD)
- Le système est **complètement extensible** — modifiez la logique dans `calculerScore()` si besoin

---

## 🚀 Prochaines Étapes (Optionnel)

- [ ] Enregistrer les score/badge en base de données pour historique
- [ ] Afficher les statistiques dans le dashboard admin
- [ ] Ajouter des notifications pour changements de statut
- [ ] Créer des reports détaillés d'engagement utilisateur
