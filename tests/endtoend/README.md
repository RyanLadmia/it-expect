# Tests End-to-End - Cinetech

Ce dossier contient les tests end-to-end pour l'application Cinetech, implémentés avec **Cypress** et **Playwright**.

## 📋 Vue d'ensemble

Les tests E2E vérifient le comportement complet de l'application du point de vue de l'utilisateur final :
- ✅ Navigation entre les pages
- ✅ Authentification (inscription, connexion, déconnexion)
- ✅ Gestion du profil utilisateur
- ✅ Système de favoris (ajout, suppression)
- ✅ Système de commentaires et réponses
- ✅ Fonctionnalités AJAX
- ✅ Responsive design
- ✅ Performance et accessibilité

## 🛠️ Installation

### Cypress

```bash
# Installation de Cypress
npm install cypress --save-dev

# Installation des dépendances additionnelles
npm install @cypress/grep --save-dev
```

### Playwright

```bash
# Installation de Playwright
npm install @playwright/test --save-dev

# Installation des navigateurs
npx playwright install
```

## 🚀 Exécution des tests

### Cypress

```bash
# Interface graphique (recommandé pour le développement)
npx cypress open

# Mode headless (pour CI/CD)
npx cypress run

# Tests spécifiques
npx cypress run --spec "tests/endtoend/cypress-e2e-tests.cy.js"

# Tests avec un navigateur spécifique
npx cypress run --browser chrome
npx cypress run --browser firefox
npx cypress run --browser edge
```

### Playwright

```bash
# Exécuter tous les tests
npx playwright test

# Mode interface graphique
npx playwright test --ui

# Tests sur un navigateur spécifique
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit

# Tests en mode debug
npx playwright test --debug

# Tests avec rapport HTML
npx playwright test --reporter=html
```

## 📁 Structure des fichiers

```
tests/endtoend/
├── cypress-e2e-tests.cy.js          # Tests Cypress
├── playwright-e2e-tests.spec.js     # Tests Playwright
├── README.md                        # Ce fichier
└── screenshots/                     # Captures d'écran (généré)
```

## 🔧 Configuration

### Variables d'environnement

Créez un fichier `.env` à la racine du projet :

```env
# Configuration de test
BASE_URL=http://localhost/it-expect
TEST_USER_EMAIL=test.e2e@example.com
TEST_USER_PASSWORD=TestPassword123!

# Base de données de test (optionnel)
DB_TEST_HOST=localhost
DB_TEST_NAME=cinetech_test
DB_TEST_USER=test_user
DB_TEST_PASS=test_password
```

### Configuration du serveur local

Assurez-vous que votre serveur local est démarré :

```bash
# Avec MAMP/XAMPP
# Démarrez Apache et MySQL depuis l'interface

# Ou avec PHP intégré
php -S localhost:8000

# Ou avec un autre serveur local
# Ajustez l'URL dans les fichiers de configuration
```

## 📊 Types de tests inclus

### 1. Tests de Navigation
- Chargement des pages principales
- Navigation entre les sections
- Menu responsive
- Gestion des erreurs 404

### 2. Tests d'Authentification
- Inscription de nouveaux utilisateurs
- Connexion avec identifiants valides/invalides
- Déconnexion
- Gestion des sessions

### 3. Tests de Profil
- Affichage des informations
- Modification des données personnelles
- Suppression de compte
- Validation des formulaires

### 4. Tests de Favoris
- Ajout/suppression de favoris
- Affichage de la liste des favoris
- Interactions AJAX
- Confirmations utilisateur

### 5. Tests de Commentaires
- Ajout de commentaires
- Ajout de réponses
- Suppression de commentaires
- Validation du contenu

### 6. Tests de Performance
- Temps de chargement
- Optimisation des ressources
- Tests de charge basique

### 7. Tests d'Accessibilité
- Attributs alt sur les images
- Labels sur les formulaires
- Navigation au clavier
- Contraste et lisibilité

## 🐛 Débogage

### Cypress

```bash
# Mode debug avec interface graphique
npx cypress open

# Logs détaillés
DEBUG=cypress:* npx cypress run

# Captures d'écran automatiques en cas d'échec
# (configuré automatiquement)
```

### Playwright

```bash
# Mode debug interactif
npx playwright test --debug

# Traces détaillées
npx playwright test --trace on

# Captures d'écran et vidéos
npx playwright test --screenshot=only-on-failure --video=retain-on-failure

# Rapport HTML avec détails
npx playwright show-report
```

## 📈 Intégration CI/CD

### GitHub Actions (exemple)

```yaml
name: E2E Tests
on: [push, pull_request]

jobs:
  cypress:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: cypress-io/github-action@v5
        with:
          start: php -S localhost:8000
          wait-on: 'http://localhost:8000'

  playwright:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - name: Install dependencies
        run: npm ci
      - name: Install Playwright
        run: npx playwright install --with-deps
      - name: Run tests
        run: npx playwright test
```

## 🎯 Bonnes pratiques

### Sélecteurs robustes
```javascript
// ✅ Bon : utiliser des attributs de test
cy.get('[data-testid="login-button"]')

// ✅ Bon : utiliser du texte visible
cy.get('button:contains("Se connecter")')

// ❌ Éviter : sélecteurs CSS fragiles
cy.get('.btn-primary.auth-btn')
```

### Attentes et assertions
```javascript
// ✅ Attendre les éléments
await expect(page.locator('.success-message')).toBeVisible();

// ✅ Attendre les réponses réseau
await page.waitForResponse('**/api/login');

// ✅ Vérifications multiples
await expect(page).toHaveTitle(/Cinetech/);
await expect(page).toHaveURL(/home/);
```

### Isolation des tests
```javascript
// ✅ Nettoyer avant chaque test
beforeEach(() => {
  cy.clearCookies();
  cy.clearLocalStorage();
});

// ✅ Données de test indépendantes
const testUser = `test-${Date.now()}@example.com`;
```

## 🔍 Résolution des problèmes courants

### Timeouts
```javascript
// Augmenter les timeouts si nécessaire
cy.get('.slow-element', { timeout: 10000 });
await page.waitForSelector('.slow-element', { timeout: 10000 });
```

### Éléments dynamiques
```javascript
// Attendre que l'élément soit stable
await page.waitForLoadState('networkidle');
await page.waitForFunction(() => document.readyState === 'complete');
```

### Tests flaky
```javascript
// Ajouter des retry automatiques
test.describe.configure({ retries: 2 });

// Ou attendre des conditions spécifiques
await page.waitForFunction(() => window.jQuery !== undefined);
```

## 📝 Rapport de bugs

Quand un test échoue :
1. 📸 Vérifiez les captures d'écran générées
2. 🎥 Regardez les vidéos d'échec (Playwright)
3. 📋 Consultez les logs détaillés
4. 🔄 Reproduisez manuellement le scénario
5. 🐛 Créez un rapport de bug avec les éléments ci-dessus

## 📞 Support

Pour toute question sur les tests E2E :
- 📖 Consultez la documentation officielle [Cypress](https://docs.cypress.io/) et [Playwright](https://playwright.dev/)
- 🔍 Recherchez dans les issues existantes
- 💬 Contactez l'équipe de développement
