# Ina Zaoui

## 🛠️ Technologies Utilisées
##### PHP 8.3
##### Symfony 6.4 (LTS)
##### Visual Studio Code (VSC)
##### PHPUnit pour les tests
##### PHPStan pour l'analyse statique

## Installation 

### Composer
Dans un premier temps, installer les dépendances :
```bash
composer install
```

## Configuration

### Base de données
Actuellement, le fichier `.env` est configuré pour la base de données MySQL.
Cependant, vous pouvez créer un fichier `.env.local` si nécessaire pour configurer l'accès à la base de données.
Exemple :
```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/inaZaoui?serverVersion=8.0.32&charset=utf8mb4"
```

### PHP (optionnel)
Vous pouvez surcharger la configuration PHP en créant un fichier `php.local.ini`.

De même pour la version de PHP que vous pouvez spécifier dans un fichier `.php-version`.

## Usage

### Base de données

#### Supprimer la base de données
```bash
symfony console doctrine:database:drop --force
```

#### Créer la base de données
```bash
symfony console doctrine:database:create
```

#### Charger le schéma de la base de données
```bash
symfony console doctrine:schema:update --force
```

#### Charger les fixtures
```bash
symfony console doctrine:fixtures:load
```

*Note : Vous pouvez exécuter ces commandes avec l'option `--env=test` pour les exécuter dans l'environnement de test. Cela sera indispensable pour pouvoir effectuer les tests qui vont suivre*

### ✅ Tests
Exécution des Tests
Avant de lancer les tests, assurez-vous que les fixtures sont bien chargées dans l'environnement de test :

Préparer la base de données de test :

```bash
symfony console doctrine:database:drop --force --env=test
symfony console doctrine:database:create --env=test
symfony console doctrine:schema:update --force --env=test 
symfony console doctrine:fixtures:load --env=test
```

Exécuter les tests avec PHPUnit :

```bash
symfony php bin/phpunit
```

Analyse du code avec PHPStan (vérification des types, etc.) :

```bash
vendor/bin/phpstan analyse src --memory-limit=256M
```

### Génération du rapport de couverture des tests

Pour générer un rapport de couverture de code avec PHPUnit, vous pouvez exécuter les commandes suivantes avec GitBash :

```bash
rm -rf cover
mkdir cover
export XDEBUG_MODE=coverage
php -d memory_limit=-1 vendor/bin/phpunit --coverage-html cover --testdox --stop-on-failure
```

Après l'exécution, vous pouvez ouvrir le fichier index.html générés dans le dossier `cover` avec votre navigateur pour visualiser le rapport détaillé de couverture de code.

### Optimisation des images

## Instructions

Pour effectuer des tests, un dossier dédié existe pour stocker les images : `public/uploads/nature` (le dossier "nature" doit être créé s'il n'existe pas). Ce dossier constitue un "album" de test destiné au chargement des fixtures.

### Étapes

1. **Préparation des images**
   - Placez les images dans le dossier `./nature`.

2. **Utilisation de la méthode OptimizeImage**
   - Cette méthode est présente dans le `ResizerController`.
   - **Note importante** : Vous devez disposer des droits administrateur/trice pour appeler cette méthode directement depuis le site.

3. **Optimisation et conversion**
   - La méthode `OptimizeImage()` a pour objectif d’optimiser et de reformater les images présentes dans le dossier `public/uploads/nature`.
   - Les images optimisées seront enregistrées dans le dossier `public/uploadsResized/nature`.

4. **Format et allègement**
   - Les images seront converties au format **webp** (format adapté aux sites web).
   - Elles seront également allégées pour améliorer les performances.

---

### Résumé des dossiers impliqués

- **Dossier source** : `public/uploads/nature`
- **Dossier cible** : `public/uploadsResized/nature`

---

⚠️ **Prérequis** : Assurez-vous que le dossier "nature" existe dans les deux répertoires avant de lancer l'optimisation.


### Serveur web
```bash
symfony server:start
```
