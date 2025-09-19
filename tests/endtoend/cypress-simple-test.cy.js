/**
 * TEST SIMPLE CYPRESS - VÉRIFICATION DE CONFIGURATION
 * 
 * Ce test simple vérifie que Cypress est correctement configuré
 * et peut accéder à l'application Cinetech
 */

describe('Configuration Cypress - Test Simple', () => {
  it('devrait charger la page d\'accueil', () => {
    cy.visit('/');
    
    // Vérifications de base
    cy.get('header').should('be.visible');
    cy.get('.logo').should('be.visible');
    cy.title().should('contain', 'Cinetech');
    
    cy.log('✅ Configuration Cypress fonctionne correctement !');
  });

  it('devrait pouvoir utiliser les commandes personnalisées', () => {
    // Test de la commande visitCinetech
    cy.visitCinetech('home');
    cy.checkBasicLayout();
    
    // Test de la commande checkAccessibility
    cy.checkAccessibility();
    
    cy.log('✅ Commandes personnalisées fonctionnent !');
  });

  it('devrait pouvoir charger les fixtures', () => {
    // Charger les données de test
    cy.fixture('users').then((users) => {
      expect(users).to.have.property('testUser');
      expect(users.testUser).to.have.property('email');
    });

    cy.fixture('content').then((content) => {
      expect(content).to.have.property('movies');
      expect(content.movies).to.be.an('array');
    });

    cy.log('✅ Fixtures chargées correctement !');
  });
});
