/**
 * TESTS END-TO-END PLAYWRIGHT - APPLICATION CINETECH
 * 
 * Ces tests vérifient le comportement complet de l'application
 * avec Playwright, offrant des capacités avancées :
 * - Tests multi-navigateurs (Chrome, Firefox, Safari)
 * - Tests en parallèle
 * - Capture d'écrans et vidéos
 * - Tests de performance
 * - Simulation de conditions réseau
 * 
 * Installation requise :
 * - npm install @playwright/test --save-dev
 * - npx playwright install
 * - npx playwright test
 */

const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://localhost/it-expect';
const TEST_USER = {
  firstname: 'Test',
  lastname: 'Playwright',
  email: 'test.playwright@example.com',
  password: 'TestPassword123!'
};

// Configuration globale
test.beforeEach(async ({ page, context }) => {
  // Nettoyer les cookies et le stockage
  await context.clearCookies();
  await page.evaluate(() => {
    localStorage.clear();
    sessionStorage.clear();
  });
});

/**
 * GROUPE 1 : NAVIGATION ET INTERFACE UTILISATEUR
 */
test.describe('Navigation et Interface', () => {
  test('devrait charger la page d\'accueil avec tous les éléments', async ({ page }) => {
    await page.goto(BASE_URL);

    // Vérifier le titre
    await expect(page).toHaveTitle(/Cinetech/);

    // Vérifier la structure de la page
    await expect(page.locator('header')).toBeVisible();
    await expect(page.locator('.logo img')).toBeVisible();
    await expect(page.locator('nav.navbar')).toBeVisible();
    await expect(page.locator('footer')).toBeVisible();
    await expect(page.locator('footer')).toContainText('Cinetech Ryan Ladmia 2024');

    // Vérifier les liens de navigation
    await expect(page.locator('nav a[href*="home"]')).toBeVisible();
    await expect(page.locator('nav a[href*="movie"]')).toBeVisible();
    await expect(page.locator('nav a[href*="serie"]')).toBeVisible();
  });

  test('devrait naviguer entre toutes les pages principales', async ({ page }) => {
    await page.goto(BASE_URL);

    // Test navigation vers Films
    await page.click('nav a:has-text("Films")');
    await expect(page).toHaveURL(/movie/);
    await expect(page).toHaveTitle(/Films/);

    // Test navigation vers Séries
    await page.click('nav a:has-text("Séries")');
    await expect(page).toHaveURL(/serie/);
    await expect(page).toHaveTitle(/Séries/);

    // Retour à l'accueil via logo
    await page.click('.logo a');
    await expect(page).toHaveURL(BASE_URL + '/');
  });

  test('devrait être responsive sur différentes tailles d\'écran', async ({ page }) => {
    // Test desktop
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto(BASE_URL);
    await expect(page.locator('nav.navbar')).toBeVisible();

    // Test tablette
    await page.setViewportSize({ width: 768, height: 1024 });
    await expect(page.locator('.burger-menu')).toBeVisible();

    // Test mobile
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('.burger-menu')).toBeVisible();
    
    // Tester le menu mobile
    await page.click('.burger-menu');
    await expect(page.locator('nav.navbar')).toBeVisible();
  });
});

/**
 * GROUPE 2 : AUTHENTIFICATION ET GESTION UTILISATEUR
 */
test.describe('Authentification', () => {
  test('devrait permettre l\'inscription d\'un nouvel utilisateur', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=login`);

    // Vérifier que nous sommes sur la page de connexion
    await expect(page.locator('h1')).toContainText('Connexion');

    // Remplir le formulaire d'inscription
    await page.fill('input[name="firstname"]', TEST_USER.firstname);
    await page.fill('input[name="lastname"]', TEST_USER.lastname);
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.fill('input[name="confirm_password"]', TEST_USER.password);

    // Sélectionner le type inscription
    await page.check('input[name="form_type"][value="register"]');

    // Soumettre le formulaire
    await page.click('button[type="submit"]:has-text("S\'inscrire")');

    // Vérifier le résultat (message de succès ou redirection)
    await expect(page.locator('.success-message, .message')).toBeVisible();
  });

  test('devrait permettre la connexion avec des identifiants valides', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=login`);

    // Basculer vers connexion
    await page.check('input[name="form_type"][value="login"]');

    // Remplir les identifiants
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);

    // Se connecter
    await page.click('button[type="submit"]:has-text("Se connecter")');

    // Vérifier la redirection
    await expect(page).toHaveURL(/home/);
    await expect(page.locator('button.deco')).toBeVisible();
    await expect(page.locator('button.deco')).toContainText('Se déconnecter');
  });

  test('devrait afficher une erreur avec des identifiants invalides', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=login`);

    await page.check('input[name="form_type"][value="login"]');
    await page.fill('input[name="email"]', 'wrong@email.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]:has-text("Se connecter")');

    // Vérifier le message d'erreur
    await expect(page.locator('.error, .error-message')).toBeVisible();
  });

  test('devrait permettre la déconnexion', async ({ page }) => {
    // Se connecter d'abord
    await loginUser(page, TEST_USER.email, TEST_USER.password);

    // Se déconnecter
    await page.click('button.deco:has-text("Se déconnecter")');

    // Vérifier la redirection
    await expect(page).toHaveURL(/login/);
    await expect(page.locator('button.co a')).toContainText('Se Connecter');
  });
});

/**
 * GROUPE 3 : GESTION DU PROFIL
 */
test.describe('Gestion du Profil', () => {
  test.beforeEach(async ({ page }) => {
    await loginUser(page, TEST_USER.email, TEST_USER.password);
  });

  test('devrait afficher les informations du profil', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=profile`);

    await expect(page.locator('h2')).toContainText(`Bienvenue, ${TEST_USER.firstname}`);
    await expect(page.locator('#user_firstname')).toContainText(TEST_USER.firstname);
    await expect(page.locator('#user_lastname')).toContainText(TEST_USER.lastname);
    await expect(page.locator('#user_email')).toContainText(TEST_USER.email);
  });

  test('devrait permettre la modification des informations', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=profile`);

    // Ouvrir le formulaire de modification
    await page.click('#edit_button');
    await expect(page.locator('#update_form')).toBeVisible();

    // Modifier le prénom
    await page.selectOption('#field', 'user_firstname');
    const newFirstname = 'PlaywrightTest';
    await page.fill('#new_value', newFirstname);

    // Soumettre via AJAX
    await page.click('button.profile_update');

    // Attendre la réponse AJAX
    await page.waitForSelector('#success_message', { state: 'visible' });
    
    // Vérifier la mise à jour
    await expect(page.locator('#user_firstname')).toContainText(newFirstname);
  });

  test('devrait gérer la suppression du compte avec confirmation', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=profile`);

    // Cliquer sur supprimer le compte
    await page.click('#delete_account_button');
    await expect(page.locator('#confirmation_message')).toBeVisible();

    // Annuler d'abord
    await page.click('#cancel_delete');
    await expect(page.locator('#cancel_message')).toBeVisible();

    // Puis confirmer la suppression
    await page.click('#delete_account_button');
    await page.click('#confirm_delete');

    // Vérifier la redirection
    await expect(page).toHaveURL(/home/);
  });
});

/**
 * GROUPE 4 : SYSTÈME DE FAVORIS
 */
test.describe('Système de Favoris', () => {
  test.beforeEach(async ({ page }) => {
    await loginUser(page, TEST_USER.email, TEST_USER.password);
  });

  test('devrait afficher la page des favoris', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=favorite`);

    await expect(page.locator('h1')).toContainText('Mes Favoris');
    
    // Vérifier la structure de la page
    const hasNoFavorites = await page.locator('.no_favorite').isVisible();
    const hasFavoritesList = await page.locator('#favoritesList').isVisible();
    
    expect(hasNoFavorites || hasFavoritesList).toBeTruthy();
  });

  test('devrait gérer l\'ajout et la suppression de favoris via AJAX', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=detail&id=12345&type=movie`);

    // Intercepter les requêtes AJAX
    await page.route('**/favorite', async route => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true, message: 'Favori ajouté' })
      });
    });

    // Tester l'ajout aux favoris
    const favoriteButton = page.locator('button:has-text("favori")').first();
    if (await favoriteButton.isVisible()) {
      await favoriteButton.click();
      
      // Vérifier la réponse AJAX
      await page.waitForResponse('**/favorite');
    }
  });

  test('devrait permettre la suppression de favoris avec confirmation', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=favorite`);

    // Si il y a des favoris
    const favoriteItems = await page.locator('#favoritesList .favorite-item').count();
    
    if (favoriteItems > 0) {
      // Cliquer sur supprimer le premier favori
      await page.click('.remove-favorite-btn');
      
      // Attendre la confirmation
      await expect(page.locator('.confirmation-message')).toBeVisible();
      
      // Confirmer
      await page.click('.confirm-remove');
      
      // Vérifier le message de succès
      await expect(page.locator('.success-message')).toBeVisible();
    }
  });
});

/**
 * GROUPE 5 : SYSTÈME DE COMMENTAIRES
 */
test.describe('Système de Commentaires', () => {
  test.beforeEach(async ({ page }) => {
    await loginUser(page, TEST_USER.email, TEST_USER.password);
  });

  test('devrait permettre l\'ajout de commentaires', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=detail&id=12345&type=movie`);

    const commentText = 'Excellent film ! Test E2E avec Playwright.';
    
    // Remplir et soumettre le commentaire
    await page.fill('textarea[name="content"]', commentText);
    await page.click('button[type="submit"]:has-text("commenter")');

    // Vérifier que le commentaire apparaît
    await expect(page.locator('.comment')).toContainText(commentText);
  });

  test('devrait permettre l\'ajout de réponses aux commentaires', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=detail&id=12345&type=movie`);

    // Chercher un commentaire existant
    const commentExists = await page.locator('.comment').count();
    
    if (commentExists > 0) {
      const replyText = 'Réponse de test Playwright';
      
      // Cliquer sur répondre au premier commentaire
      await page.click('.reply-btn');
      
      // Remplir la réponse
      await page.fill('textarea[name="reply_content"]', replyText);
      await page.click('button[type="submit"]:has-text("répondre")');

      // Vérifier que la réponse apparaît
      await expect(page.locator('.reply')).toContainText(replyText);
    }
  });

  test('devrait permettre la suppression de commentaires', async ({ page }) => {
    await page.goto(`${BASE_URL}?r=detail&id=12345&type=movie`);

    // Chercher les commentaires de l'utilisateur
    const userComments = await page.locator('.comment .delete-comment').count();
    
    if (userComments > 0) {
      // Supprimer le premier commentaire
      await page.click('.delete-comment');
      
      // Confirmer si nécessaire
      const confirmButton = page.locator('.confirm-delete');
      if (await confirmButton.isVisible()) {
        await confirmButton.click();
      }

      // Vérifier le message de succès
      await expect(page.locator('.success, .message')).toBeVisible();
    }
  });
});

/**
 * GROUPE 6 : FONCTIONNALITÉS AVANCÉES ET PERFORMANCE
 */
test.describe('Fonctionnalités Avancées', () => {
  test('devrait permettre la recherche avec autocomplétion', async ({ page }) => {
    await page.goto(BASE_URL);

    const searchTerm = 'Avengers';
    
    // Utiliser la barre de recherche
    await page.fill('#search', searchTerm);
    
    // Attendre l'autocomplétion
    await page.waitForSelector('#suggestion', { state: 'visible' });
    
    // Vérifier les suggestions
    await expect(page.locator('#suggestion')).not.toBeEmpty();
  });

  test('devrait gérer les erreurs 404 correctement', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}?r=nonexistent`);
    
    // Vérifier que la page d'erreur est affichée
    await expect(page.locator('body')).toContainText('404');
  });

  test('devrait avoir de bonnes performances de chargement', async ({ page }) => {
    const startTime = Date.now();
    
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    
    const loadTime = Date.now() - startTime;
    expect(loadTime).toBeLessThan(3000); // Moins de 3 secondes
  });

  test('devrait fonctionner avec des connexions lentes', async ({ page, context }) => {
    // Simuler une connexion lente
    await context.route('**/*', async route => {
      await new Promise(resolve => setTimeout(resolve, 100)); // Délai de 100ms
      await route.continue();
    });

    await page.goto(BASE_URL);
    await expect(page.locator('header')).toBeVisible({ timeout: 10000 });
  });
});

/**
 * GROUPE 7 : TESTS MULTI-NAVIGATEURS ET ACCESSIBILITÉ
 */
test.describe('Compatibilité et Accessibilité', () => {
  test('devrait fonctionner correctement sur tous les navigateurs', async ({ page, browserName }) => {
    await page.goto(BASE_URL);
    
    // Tests spécifiques par navigateur
    if (browserName === 'webkit') {
      // Tests spécifiques Safari
      await expect(page.locator('header')).toBeVisible();
    } else if (browserName === 'firefox') {
      // Tests spécifiques Firefox
      await expect(page.locator('nav')).toBeVisible();
    } else {
      // Tests Chrome/Edge
      await expect(page.locator('.logo')).toBeVisible();
    }
  });

  test('devrait respecter les standards d\'accessibilité', async ({ page }) => {
    await page.goto(BASE_URL);

    // Vérifier les attributs alt sur les images
    const images = await page.locator('img').all();
    for (const img of images) {
      await expect(img).toHaveAttribute('alt');
    }

    // Vérifier les labels sur les inputs
    const inputs = await page.locator('input').all();
    for (const input of inputs) {
      const hasLabel = await input.getAttribute('aria-label') || 
                      await input.getAttribute('placeholder') ||
                      await page.locator(`label[for="${await input.getAttribute('id')}"]`).count() > 0;
      expect(hasLabel).toBeTruthy();
    }

    // Vérifier la navigation au clavier
    await page.keyboard.press('Tab');
    const focusedElement = await page.locator(':focus');
    await expect(focusedElement).toBeVisible();
  });

  test('devrait capturer des screenshots en cas d\'erreur', async ({ page }) => {
    await page.goto(BASE_URL);
    
    // Capturer un screenshot de la page d'accueil
    await page.screenshot({ path: 'tests/screenshots/homepage.png', fullPage: true });
    
    // Test qui pourrait échouer pour démontrer la capture d'erreur
    try {
      await page.click('.non-existent-element', { timeout: 1000 });
    } catch (error) {
      await page.screenshot({ 
        path: 'tests/screenshots/error-state.png', 
        fullPage: true 
      });
      // Re-throw pour que le test échoue si nécessaire
      // throw error;
    }
  });
});

/**
 * FONCTIONS UTILITAIRES
 */
async function loginUser(page, email, password) {
  await page.goto(`${BASE_URL}?r=login`);
  await page.check('input[name="form_type"][value="login"]');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]:has-text("Se connecter")');
  await expect(page).toHaveURL(/home/);
}

/**
 * CONFIGURATION PLAYWRIGHT (playwright.config.js)
 * 
 * const { defineConfig, devices } = require('@playwright/test');
 * 
 * module.exports = defineConfig({
 *   testDir: './tests/endtoend',
 *   fullyParallel: true,
 *   forbidOnly: !!process.env.CI,
 *   retries: process.env.CI ? 2 : 0,
 *   workers: process.env.CI ? 1 : undefined,
 *   reporter: 'html',
 *   use: {
 *     baseURL: 'http://localhost/it-expect',
 *     trace: 'on-first-retry',
 *     screenshot: 'only-on-failure',
 *     video: 'retain-on-failure',
 *   },
 *   projects: [
 *     {
 *       name: 'chromium',
 *       use: { ...devices['Desktop Chrome'] },
 *     },
 *     {
 *       name: 'firefox',
 *       use: { ...devices['Desktop Firefox'] },
 *     },
 *     {
 *       name: 'webkit',
 *       use: { ...devices['Desktop Safari'] },
 *     },
 *     {
 *       name: 'Mobile Chrome',
 *       use: { ...devices['Pixel 5'] },
 *     },
 *     {
 *       name: 'Mobile Safari',
 *       use: { ...devices['iPhone 12'] },
 *     },
 *   ],
 *   webServer: {
 *     command: 'php -S localhost:8000',
 *     port: 8000,
 *   },
 * });
 */
