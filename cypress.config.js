const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    // URL de base de votre application
    baseUrl: 'http://localhost/it-expect',
    
    // Configuration de la fenêtre
    viewportWidth: 1280,
    viewportHeight: 720,
    
    // Enregistrement vidéo et captures d'écran
    video: true,
    screenshotOnRunFailure: true,
    
    // Timeouts
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000,
    pageLoadTimeout: 30000,
    
    // Dossier des tests
    specPattern: 'tests/endtoend/**/*.cy.{js,jsx,ts,tsx}',
    
    // Dossiers de sortie
    videosFolder: 'tests/cypress/videos',
    screenshotsFolder: 'tests/cypress/screenshots',
    
    // Configuration des tests
    supportFile: 'tests/cypress/support/e2e.js',
    fixturesFolder: 'tests/cypress/fixtures',
    
    setupNodeEvents(on, config) {
      // Configuration des événements Node.js
      
      // Plugin pour les tâches personnalisées
      on('task', {
        // Tâche pour nettoyer la base de données de test
        clearTestData() {
          // Implémentez ici la logique pour nettoyer les données de test
          console.log('Nettoyage des données de test...');
          return null;
        },
        
        // Tâche pour créer des données de test
        seedTestData() {
          // Implémentez ici la logique pour créer des données de test
          console.log('Création des données de test...');
          return null;
        }
      });
      
      // Configuration pour différents environnements
      if (config.env.environment === 'staging') {
        config.baseUrl = 'https://staging.cinetech.com';
      } else if (config.env.environment === 'production') {
        config.baseUrl = 'https://cinetech.com';
      }
      
      return config;
    },
    
    // Variables d'environnement
    env: {
      // Ajustez selon votre configuration
      environment: 'local',
      apiUrl: 'http://localhost/it-expect',
      
      // Données de test
      testUser: {
        email: 'test.cypress@example.com',
        password: 'TestPassword123!'
      }
    },
    
    // Configuration expérimentale
    experimentalStudio: true,
    experimentalWebKitSupport: false
  },
  
  // Configuration pour les tests de composants (si nécessaire)
  component: {
    devServer: {
      framework: 'create-react-app',
      bundler: 'webpack',
    },
  },
});
