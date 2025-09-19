/**
 * TEST SIMPLE PLAYWRIGHT - VÉRIFICATION DE CONFIGURATION
 * 
 * Ce test simple vérifie que Playwright est correctement configuré
 * et peut accéder à l'application Cinetech
 */

const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://localhost:8888/it-expect';

test.describe('Configuration Playwright - Test Simple', () => {
  test('devrait charger la page d\'accueil', async ({ page }) => {
    await page.goto(BASE_URL);
    
    // Vérifications de base
    await expect(page.locator('header')).toBeVisible();
    await expect(page.locator('.logo')).toBeVisible();
    await expect(page).toHaveTitle(/Cinetech/);
    
    console.log('✅ Configuration Playwright fonctionne correctement !');
  });

  test('devrait pouvoir naviguer vers les pages principales', async ({ page }) => {
    await page.goto(BASE_URL);
    
    // Test navigation vers Films
    await page.click('nav a:has-text("Films")');
    await expect(page).toHaveURL(/movie/);
    
    // Test navigation vers Séries  
    await page.click('nav a:has-text("Séries")');
    await expect(page).toHaveURL(/serie/);
    
    console.log('✅ Navigation fonctionne correctement !');
  });

  test('devrait être responsive', async ({ page }) => {
    await page.goto(BASE_URL);
    
    // Test desktop
    await page.setViewportSize({ width: 1280, height: 720 });
    await expect(page.locator('nav.navbar')).toBeVisible();
    
    // Test mobile
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('.burger-menu')).toBeVisible();
    
    console.log('✅ Design responsive fonctionne !');
  });
});
