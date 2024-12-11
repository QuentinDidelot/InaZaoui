# Contribuer au projet Ina Zaoui

Merci de votre intérêt pour contribuer à ce projet ! Ce guide vous aidera à comprendre comment contribuer efficacement.

## 🚀 Comment contribuer ?

1. **Fork du projet**  
   Créez un fork de ce dépôt sur votre compte GitHub. Clonez-le ensuite localement :
   ```bash
   git clone https://github.com/votre-utilisateur/ina-zaoui.git
   ```

2. **Créer une branche**  
   Travaillez sur une nouvelle branche pour chaque fonctionnalité ou correction :
   ```bash
   git checkout -b feature/nom-de-la-feature
   ```

3. **Proposer des changements**  
   Après vos modifications, poussez votre branche :
   ```bash
   git add .
   git commit -m "Description de votre modification"
   git push origin feature/nom-de-la-feature
   ```
   Ensuite, ouvrez une Pull Request depuis votre dépôt forké vers le dépôt principal.

4. **Idées d'ajout**
    - Un système de filtre pour les utilisateurs / portfolio
    - Une amélioration/optimisation du code déjà existant
    - Surprenez moi !
    
5. **Revue et approbation**  
   Un mainteneur examinera votre Pull Request. Si des changements sont demandés, effectuez-les sur votre branche existante.


## 🎨 Style de code

Ce projet suit les standards PSR-12 pour le code PHP. Utilisez des outils comme `phpcs` pour valider le style :
```bash
composer require --dev squizlabs/php_codesniffer
vendor/bin/phpcs --standard=PSR12 src/
```

## ✅ Tests et analyse statique

Avant de soumettre une contribution, assurez-vous que vos modifications respectent les règles de qualité du code.

1. **Tests unitaires**  
   Exécutez les tests avec PHPUnit :
   ```bash
   symfony php bin/phpunit
   ```

2. **Couverture des tests**  
   Si vous avez Xdebug activé, générez un rapport de couverture :
   ```bash
   rm -rf cover
   mkdir cover
   export XDEBUG_MODE=coverage
   php -d memory_limit=-1 vendor/bin/phpunit --coverage-html cover --testdox --stop-on-failure
   ```
   Ouvrez le fichier `cover/index.html` dans votre navigateur pour voir le rapport.

3. **Analyse statique**  
   Vérifiez les types et autres problèmes avec PHPStan :
   ```bash
   vendor/bin/phpstan analyse src --memory-limit=256M
   ```

## 💡 Bonnes pratiques

- **Commits clairs** : Rédigez des messages de commit concis et explicites. Suivez la convention *Conventional Commits* si possible.
- **Documentation** : Si votre contribution implique une nouvelle fonctionnalité, mettez à jour le README ou la documentation associée.
- **Pull Requests** : Incluez une description claire de vos modifications dans la Pull Request.

## 📄 Code of Conduct

Ce projet respecte un code de conduite basé sur [Contributor Covenant](https://www.contributor-covenant.org). Merci de maintenir un environnement respectueux et inclusif.
