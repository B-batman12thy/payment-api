# Payment API (Laravel + JWT)

API de paiements (mock) avec authentification **JWT**, upload de justificatifs, statistiques et dashboard.
Back-end en **Laravel**, base SQLite pour un setup rapide, prêt à être consommé par un front Flutter.

---

## ✨ Fonctionnalités

- Auth JWT : `register`, `login`, `me`, `logout`, `refresh`
- Paiements :
  - Création d’un paiement (JSON ou `multipart` avec fichier justificatif)
  - Listing + filtres `?day=YYYY-MM-DD` | `?month=YYYY-MM` | `?year=YYYY`
  - Téléchargement du justificatif
- Dashboard : solde simulé, total du mois, 5 derniers paiements
- Statistics : agrégation par **statut** et **catégorie**

---

## 🧰 Stack & Prérequis

- PHP 8.2+
- Composer
- SQLite (par défaut)
- Laravel
- jwt-auth (`tymon/jwt-auth`)

---

## 🚀 Installation (SQLite)

```bash
# 1) Dépendances
composer install

# 2) .env
cp .env.example .env
php artisan key:generate

# 3) Base SQLite
rm -f database/database.sqlite && touch database/database.sqlite

# 4) JWT
composer require tymon/jwt-auth:^2.0
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret   # => ajoute JWT_SECRET=... dans .env

# 5) Migrations + storage (pour les justificatifs)
php artisan migrate:fresh
php artisan storage:link

# 6) Nettoyage des caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear

# 7) Lancer le serveur
php artisan serve
