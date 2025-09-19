# Guide d'installation Cypress - Solutions pour macOS

## 🚨 Problème rencontré

Cypress rencontre des difficultés sur votre système macOS avec l'erreur :
```
/Users/ryan/Library/Caches/Cypress/15.2.0/Cypress.app/Contents/MacOS/Cypress: bad option: --no-sandbox
```

## 🛠️ Solutions recommandées

### Solution 1 : Installation avec permissions système

```bash
# Nettoyer le cache Cypress
npx cypress cache clear

# Réinstaller avec permissions
sudo npm install cypress --save-dev --unsafe-perm=true --allow-root

# Ou essayer avec une version spécifique stable
npm install cypress@13.6.0 --save-dev
```

### Solution 2 : Variables d'environnement

```bash
# Configurer les variables d'environnement
export CYPRESS_CACHE_FOLDER=~/.cypress-cache
export CYPRESS_RUN_BINARY=~/.cypress-cache/Cypress.app/Contents/MacOS/Cypress

# Puis réinstaller
npm install cypress --save-dev
```

### Solution 3 : Installation manuelle

```bash
# Télécharger manuellement Cypress
curl -o cypress.zip https://cdn.cypress.io/desktop/13.6.0/darwin-arm64/cypress.zip

# Extraire dans le cache
unzip cypress.zip -d ~/.cypress-cache/

# Réinstaller le package npm
npm install cypress --save-dev --force
```

### Solution 4 : Utiliser Docker (alternative)

Si l'installation native continue de poser problème :

```bash
# Créer un Dockerfile pour Cypress
docker run -it -v $PWD:/e2e -w /e2e cypress/included:13.6.0
```

## 🎯 Test de fonctionnement

Une fois Cypress installé, testez avec :

```bash
# Vérifier l'installation
npx cypress verify

# Lancer l'interface graphique
npx cypress open

# Ou en mode headless
npx cypress run --spec "tests/endtoend/cypress-simple-test.cy.js"
```

## ⚡ Alternative : Se concentrer sur Playwright

**Playwright fonctionne parfaitement** sur votre système ! Vous pouvez :

1. **Utiliser principalement Playwright** pour vos tests E2E
2. **Garder la structure Cypress** pour plus tard quand le problème sera résolu
3. **Avoir une couverture complète** avec Playwright seul

### Commandes Playwright fonctionnelles :

```bash
# Tests sur tous les navigateurs
npx playwright test --config=playwright.config.simple.js

# Interface graphique
npx playwright test --ui

# Tests spécifiques
npx playwright test tests/endtoend/playwright-e2e-tests.spec.js

# Rapport HTML
npx playwright show-report
```

## 📋 Status actuel

- ✅ **Playwright** : Installé et fonctionnel
- ⚠️ **Cypress** : Problème d'installation macOS (solutions ci-dessus)
- ✅ **Structure complète** : Fichiers de configuration et tests créés
- ✅ **Documentation** : Guide complet disponible

## 🚀 Prochaines étapes

1. **Démarrer votre serveur local** (MAMP/XAMPP)
2. **Tester Playwright** avec votre application
3. **Résoudre Cypress** si nécessaire (optionnel)
4. **Personnaliser les tests** selon vos besoins spécifiques
