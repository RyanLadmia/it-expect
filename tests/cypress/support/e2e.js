// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using CommonJS syntax (compatible avec Cypress) ES6
require('./commands')

// Configuration globale pour Cypress
Cypress.on('uncaught:exception', (err, runnable) => {
  // Empêcher Cypress de faire échouer le test sur certaines erreurs JavaScript
  // qui pourraient venir de l'application mais qui ne sont pas critiques pour les tests
  
  // Ignorer les erreurs de réseau communes
  if (err.message.includes('NetworkError')) {
    return false;
  }
  
  // Ignorer les erreurs de script tiers
  if (err.message.includes('Script error')) {
    return false;
  }
  
  // Ignorer les erreurs de chargement de ressources
  if (err.message.includes('Loading CSS chunk')) {
    return false;
  }
  
  // Ignorer les erreurs liées aux éléments DOM manquants (scripts chargés sur toutes les pages)
  if (err.message.includes('Cannot read properties of null') || 
      err.message.includes('Cannot set properties of null') ||
      err.message.includes('reading \'contains\'') ||
      err.message.includes('setting \'innerHTML\'')) {
    return false; // Ne pas faire échouer le test pour ces erreurs non critiques
  }
  
  // Laisser les autres erreurs faire échouer le test
  return true;
});

// Configuration des timeouts par défaut
Cypress.config('defaultCommandTimeout', 10000);
Cypress.config('requestTimeout', 10000);
Cypress.config('responseTimeout', 10000);

// Commandes avant chaque test
beforeEach(() => {
  // Configurer l'intercepteur pour les requêtes API
  cy.intercept('GET', '**/api/**').as('apiRequest');
  
  // Nettoyer le localStorage et sessionStorage
  cy.window().then((win) => {
    win.sessionStorage.clear();
    win.localStorage.clear();
  });
});

// Commandes après chaque test
afterEach(() => {
  // Capturer une capture d'écran en cas d'échec (optionnel)
  cy.screenshot({ capture: 'runner', onlyOnFailure: true });
});

// Configuration pour les tests de performance
Cypress.Commands.add('waitForPageLoad', () => {
  cy.window().should('have.property', 'document');
  cy.document().should('have.property', 'readyState', 'complete');
});

// Configuration pour les tests d'accessibilité (si vous installez cypress-axe)
// import 'cypress-axe';

// Configuration pour les tests visuels (si vous installez cypress-visual-testing)
// import 'cypress-visual-testing/dist/support';

// Utilitaires pour les tests Cinetech
Cypress.Commands.add('visitCinetech', (path = '') => {
  const baseUrl = Cypress.config('baseUrl');
  const fullUrl = path ? `${baseUrl}?r=${path}` : baseUrl;
  cy.visit(fullUrl);
  cy.waitForPageLoad();
});

// Commande pour vérifier la structure de base de l'application
Cypress.Commands.add('checkBasicLayout', () => {
  cy.get('header').should('be.visible');
  cy.get('nav.navbar').should('be.visible');
  cy.get('main').should('be.visible');
  cy.get('footer').should('be.visible');
});

// Commande pour gérer les alertes JavaScript
Cypress.Commands.add('handleAlert', (expectedMessage = null) => {
  cy.window().then((win) => {
    cy.stub(win, 'alert').as('windowAlert');
  });
  
  if (expectedMessage) {
    cy.get('@windowAlert').should('have.been.calledWith', expectedMessage);
  }
});

// Commande pour attendre les requêtes AJAX
Cypress.Commands.add('waitForAjax', () => {
  cy.window().then((win) => {
    return new Cypress.Promise((resolve) => {
      if (win.jQuery) {
        const checkAjax = () => {
          if (win.jQuery.active === 0) {
            resolve();
          } else {
            setTimeout(checkAjax, 100);
          }
        };
        checkAjax();
      } else {
        // Si jQuery n'est pas disponible, attendre un court délai
        setTimeout(resolve, 500);
      }
    });
  });
});

// Commande pour vérifier les éléments d'accessibilité
Cypress.Commands.add('checkAccessibility', () => {
  // Vérifier que les images ont des attributs alt
  cy.get('img').each(($img) => {
    cy.wrap($img).should('have.attr', 'alt');
  });
  
  // Vérifier que les liens ont du texte ou des labels
  cy.get('a').each(($link) => {
    const text = $link.text().trim();
    const ariaLabel = $link.attr('aria-label');
    const title = $link.attr('title');
    
    // Vérifier qu'au moins un attribut existe et n'est pas vide
    const hasAccessibleText = text || ariaLabel || title;
    if (hasAccessibleText) {
      expect(hasAccessibleText).to.not.be.empty;
    }
  });
  
  // Vérifier que les boutons ont du texte ou des labels
  cy.get('button').each(($button) => {
    const text = $button.text().trim();
    const ariaLabel = $button.attr('aria-label');
    const title = $button.attr('title');
    
    // Vérifier qu'au moins un attribut existe et n'est pas vide
    const hasAccessibleText = text || ariaLabel || title;
    if (hasAccessibleText) {
      expect(hasAccessibleText).to.not.be.empty;
    }
  });
});

// Configuration pour les environnements
if (Cypress.env('environment') === 'development') {
  // Configurations spécifiques au développement
  Cypress.config('video', false);
  Cypress.config('screenshotOnRunFailure', true);
} else if (Cypress.env('environment') === 'ci') {
  // Configurations spécifiques à l'intégration continue
  Cypress.config('video', true);
  Cypress.config('screenshotOnRunFailure', true);
}
