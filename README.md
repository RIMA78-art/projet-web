# NutriNova

NutriNova est une application web PHP/MySQL de gestion nutritionnelle construite en architecture MVC légère.

Le projet permet de :

- gérer des repas et des ingrédients depuis un back-office,
- calculer un plan nutritionnel personnalisé,
- proposer un profil santé multi-objectifs,
- exporter le plan en CSV/PDF,
- partager le plan via QR code et WhatsApp,
- afficher les repas les mieux notés.

## Fonctionnalités

### Front office

- Catalogue des repas
- Détail d'un repas
- Évaluations et commentaires sur les repas
- Top meals par note moyenne
- Génération d'un plan nutritionnel à partir de : poids, taille, âge, sexe, activité, objectif
- Profil multi-objectifs :
  - maintien musculaire
  - réduction sucre/sodium (proxy actuel basé sur glucides et densité énergétique)
- Export du plan nutritionnel en CSV
- Impression PDF du plan
- Partage du plan via QR code
- Partage du plan via WhatsApp

### Back office

- CRUD des repas
- CRUD des ingrédients
- Association d'ingrédients à un repas
- Recalcul automatique des macros d'un repas à partir de ses ingrédients
- Pagination des listes repas / ingrédients

### Module ML / data

Le dépôt contient aussi un dossier ml avec :

- des scripts de préparation de dataset,
- des scripts d'entraînement,
- un modèle JSON simple,
- un dataset organisé pour classification d'images.

Ce module est présent pour les expérimentations IA et peut servir à enrichir le projet (classification d'image de repas, suggestion automatique, etc.).

## Stack technique

- PHP natif
- MySQL / MariaDB
- PDO
- HTML / PHP views
- Tailwind CSS via CDN
- XAMPP recommandé en local

## Structure du projet

```text
NutriNova/
├── index.php
├── nutrinova.sql
├── config/
│   ├── config.php
│   └── Database.php
├── Controller/
│   └── NutritionController.php
├── Model/
│   ├── Meal.php
│   └── Ingredient.php
├── View/
│   ├── Front/
│   └── Back/
├── uploads/
├── ml/
│   ├── data/
│   ├── models/
│   └── scripts/
└── videos/
```

## Base de données

Le script [nutrinova.sql](nutrinova.sql) crée les principales tables suivantes :

- meals
- ingredients
- meal_ingredient
- meal_ratings

Il insère aussi des données de test pour démarrer rapidement.

## Installation locale

### 1. Prérequis

- XAMPP ou environnement équivalent
- PHP 8.x recommandé
- MySQL/MariaDB
- Navigateur web moderne

### 2. Placement du projet

Copier le dossier dans htdocs de XAMPP :

```text
c:/xampp/htdocs/NutriNova
```

### 3. Import de la base

Créer la base et importer le fichier SQL :

1. ouvrir phpMyAdmin,
2. créer une base nommée nutrinova si nécessaire,
3. importer le fichier [nutrinova.sql](nutrinova.sql).

### 4. Vérifier la configuration

Les fichiers à vérifier sont :

- [config/Database.php](config/Database.php)
- [config/config.php](config/config.php)

Configuration actuelle par défaut :

- host : localhost
- database : nutrinova
- user : root
- password : vide

L'URL applicative est définie dans [config/config.php](config/config.php) via APP_URL.

Exemple :

```php
define('APP_URL', 'http://localhost/NutriNova');
```

Si tu veux utiliser le QR code et le partage mobile sur le même réseau, remplace localhost par l'IP locale de ta machine.

### 5. Lancer l'application

Démarrer Apache et MySQL dans XAMPP puis ouvrir :

```text
http://localhost/NutriNova
```

## Principales routes

### Front

- /index.php
- /index.php?action=meal-detail&id=ID
- /index.php?action=top-meals
- /index.php?action=nutrition-plan
- /index.php?action=pdf-plan-qr
- /index.php?action=export-plan-csv

### Back office

- /index.php?action=admin-meals&section=meal
- /index.php?action=admin-meal-add&section=meal
- /index.php?action=admin-ingredients&section=ingredient
- /index.php?action=admin-ingredient-add&section=ingredient

## Logique métier actuelle

Le coeur métier est principalement concentré dans [Controller/NutritionController.php](Controller/NutritionController.php).

Exemples :

- calcul BMR / TDEE,
- génération du plan nutritionnel,
- prise en compte des priorités multi-objectifs,
- sélection des repas selon score nutritionnel,
- gestion des notes et classement des repas,
- recalcul automatique des macros des repas.

## Profil santé multi-objectifs

Le plan nutritionnel supporte plusieurs priorités combinées :

- perte de poids,
- maintien musculaire,
- réduction sucre/sodium.

Le système adapte :

- la cible calorique,
- la répartition protéines / glucides / lipides,
- le choix des repas recommandés.

Note : la réduction sucre/sodium repose actuellement sur un proxy métier, car les colonnes sucre et sodium ne sont pas encore stockées dans la base.

## Partage du plan nutritionnel

Le plan peut être partagé de plusieurs façons :

- QR code,
- lien direct,
- WhatsApp,
- export CSV,
- impression PDF.

Le partage WhatsApp tente d'ouvrir l'application WhatsApp via un deep link, avec fallback web si nécessaire.

## Journalisation et debug

Le projet active les erreurs en développement dans [config/config.php](config/config.php).

Fichiers utiles :

- logs applicatifs : config/logs/
- affichage erreurs PHP activé en développement

## Axes d'amélioration

Quelques extensions naturelles du projet :

- ajout des champs sucre et sodium dans meals / ingredients,
- contrôle qualité des données nutritionnelles,
- moteur de substitutions d'ingrédients,
- menus hebdomadaires intelligents,
- recommandations basées sur historique utilisateur,
- intégration plus poussée du module ML.

## Auteur

Projet académique / expérimental autour de la nutrition intelligente, de la recommandation de repas et de la gestion de données nutritionnelles.
