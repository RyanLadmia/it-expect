const { defineConfig, devices } = require('@playwright/test');

/**
 * Configuration Playwright pour les tests E2E de Cinetech
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  // Dossier contenant les tests
  testDir: './tests/endtoend',
  
  // Exécuter les tests en parallèle
  fullyParallel: true,
  
  // Interdire l'utilisation de test.only en CI
  forbidOnly: !!process.env.CI,
  
  // Nombre de tentatives en cas d'échec
  retries: process.env.CI ? 2 : 0,
  
  // Nombre de workers (processus parallèles)
  workers: process.env.CI ? 1 : undefined,
  
  // Reporter pour les résultats de tests
  reporter: [
    ['html', { outputFolder: 'tests/playwright-report' }],
    ['json', { outputFile: 'tests/playwright-results.json' }],
    ['junit', { outputFile: 'tests/playwright-results.xml' }]
  ],
  
  // Configuration globale pour tous les tests
  use: {
    // URL de base
    baseURL: 'http://localhost:8888/it-expect',
    
    // Trace des actions (pour le débogage)
    trace: 'on-first-retry',
    
    // Captures d'écran
    screenshot: 'only-on-failure',
    
    // Enregistrement vidéo
    video: 'retain-on-failure',
    
    // Timeouts
    actionTimeout: 10000,
    navigationTimeout: 30000,
    
    // Configuration du navigateur
    ignoreHTTPSErrors: true,
    colorScheme: 'light',
    
    // Géolocalisation (si nécessaire pour votre app)
    // geolocation: { longitude: 12.492507, latitude: 41.889938 },
    // permissions: ['geolocation'],
  },
  
  // Configuration des projets (différents navigateurs/appareils)
  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        // Configurations spécifiques à Chrome
        launchOptions: {
          args: ['--disable-web-security', '--disable-features=VizDisplayCompositor']
        }
      },
    },
    
    {
      name: 'firefox',
      use: { 
        ...devices['Desktop Firefox'],
        // Configurations spécifiques à Firefox
      },
    },
    
    {
      name: 'webkit',
      use: { 
        ...devices['Desktop Safari'],
        // Configurations spécifiques à Safari
      },
    },
    
    // Tests sur appareils mobiles
    {
      name: 'Mobile Chrome',
      use: { 
        ...devices['Pixel 5'],
      },
    },
    
    {
      name: 'Mobile Safari',
      use: { 
        ...devices['iPhone 12'],
      },
    },
    
    // Tests sur tablettes
    {
      name: 'Tablet',
      use: {
        ...devices['iPad Pro'],
      },
    },
    
    // Tests avec différentes conditions réseau
    {
      name: 'Slow Network',
      use: {
        ...devices['Desktop Chrome'],
        launchOptions: {
          args: ['--simulate-slow-connection']
        }
      },
    },
  ],
  
  // Configuration du serveur web local (optionnel)
  webServer: {
    // Si vous voulez que Playwright démarre automatiquement votre serveur
    // command: 'php -S localhost:8000',
    // port: 8000,
    // reuseExistingServer: !process.env.CI,
  },
  
  // Dossiers de sortie
  outputDir: 'tests/playwright-results/',
  
  // Configuration des hooks globaux (optionnel)
  // globalSetup: require.resolve('./tests/playwright-global-setup.js'),
  // globalTeardown: require.resolve('./tests/playwright-global-teardown.js'),
  
  // Timeout global pour tous les tests
  timeout: 30 * 1000,
  
  // Timeout pour les expect
  expect: {
    timeout: 5000,
  },
  
  // Métadonnées pour les rapports
  metadata: {
    project: 'Cinetech',
    version: '1.0.0',
    environment: process.env.NODE_ENV || 'development',
  },
});
