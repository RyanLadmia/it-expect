// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

/**
 * COMMANDES PERSONNALISÉES POUR CINETECH
 */

// Commande de connexion réutilisable
Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    cy.visitCinetech('login');
    
    // Basculer vers le formulaire de connexion
    cy.get('input[name="form_type"][value="login"]').check();
    
    // Remplir les champs
    cy.get('input[name="email"]').clear().type(email);
    cy.get('input[name="password"]').clear().type(password);
    
    // Soumettre le formulaire
    cy.get('button[type="submit"]').contains('Se connecter').click();
    
    // Vérifier la redirection vers l'accueil
    cy.url().should('include', 'home');
    cy.get('button.deco').should('be.visible').and('contain', 'Se déconnecter');
  }, {
    validate() {
      // Vérifier que la session est toujours valide
      cy.visitCinetech('profile');
      cy.get('button.deco').should('exist');
    }
  });
});

// Commande d'inscription
Cypress.Commands.add('register', (userData) => {
  cy.visitCinetech('login');
  
  // Remplir le formulaire d'inscription
  cy.get('input[name="firstname"]').clear().type(userData.firstname);
  cy.get('input[name="lastname"]').clear().type(userData.lastname);
  cy.get('input[name="email"]').clear().type(userData.email);
  cy.get('input[name="password"]').clear().type(userData.password);
  cy.get('input[name="confirm_password"]').clear().type(userData.confirmPassword || userData.password);
  
  // Sélectionner le type inscription
  cy.get('input[name="form_type"][value="register"]').check();
  
  // Soumettre le formulaire
  cy.get('button[type="submit"]').contains('S\'inscrire').click();
});

// Commande de déconnexion
Cypress.Commands.add('logout', () => {
  cy.get('button.deco').contains('Se déconnecter').click();
  cy.url().should('include', 'login');
  cy.get('button.co a').should('contain', 'Se Connecter');
});

// Commande pour ajouter un favori
Cypress.Commands.add('addToFavorites', (contentId, contentType = 'movie') => {
  cy.visitCinetech(`detail&id=${contentId}&type=${contentType}`);
  
  // Chercher et cliquer sur le bouton d'ajout aux favoris
  cy.get('button, .favorite-btn').contains(/ajouter|favori/i).first().click();
  
  // Attendre la réponse AJAX
  cy.waitForAjax();
  
  // Vérifier le message de succès
  cy.get('.success, .message').should('be.visible');
});

// Commande pour supprimer un favori
Cypress.Commands.add('removeFromFavorites', (index = 0) => {
  cy.visitCinetech('favorite');
  
  // Vérifier qu'il y a des favoris
  cy.get('#favoritesList .favorite-item').should('have.length.greaterThan', index);
  
  // Cliquer sur supprimer
  cy.get('.remove-favorite-btn').eq(index).click();
  
  // Confirmer la suppression si nécessaire
  cy.get('body').then(($body) => {
    if ($body.find('.confirm-remove').length > 0) {
      cy.get('.confirm-remove').click();
    }
  });
  
  // Attendre la réponse AJAX
  cy.waitForAjax();
});

// Commande pour ajouter un commentaire
Cypress.Commands.add('addComment', (contentId, contentType, commentText) => {
  cy.visitCinetech(`detail&id=${contentId}&type=${contentType}`);
  
  // Remplir et soumettre le commentaire
  cy.get('textarea[name="content"]').clear().type(commentText);
  cy.get('button[type="submit"]').contains(/commenter|ajouter/i).click();
  
  // Attendre la réponse
  cy.waitForAjax();
  
  // Vérifier que le commentaire apparaît
  cy.get('.comment').should('contain', commentText);
});

// Commande pour supprimer un commentaire
Cypress.Commands.add('deleteComment', (commentText) => {
  // Trouver le commentaire et le supprimer
  cy.get('.comment').contains(commentText).parent().find('.delete-comment').click();
  
  // Confirmer la suppression si nécessaire
  cy.get('body').then(($body) => {
    if ($body.find('.confirm-delete').length > 0) {
      cy.get('.confirm-delete').click();
    }
  });
  
  // Attendre la réponse AJAX
  cy.waitForAjax();
});

// Commande pour effectuer une recherche
Cypress.Commands.add('search', (searchTerm) => {
  cy.get('#search').clear().type(searchTerm);
  
  // Attendre les suggestions
  cy.get('#suggestion').should('be.visible');
  cy.waitForAjax();
  
  return cy.get('#suggestion');
});

// Commande pour modifier le profil
Cypress.Commands.add('updateProfile', (field, newValue) => {
  cy.visitCinetech('profile');
  
  // Ouvrir le formulaire de modification
  cy.get('#edit_button').click();
  cy.get('#update_form').should('be.visible');
  
  // Sélectionner le champ et entrer la nouvelle valeur
  cy.get('#field').select(field);
  cy.get('#new_value').clear().type(newValue);
  
  // Soumettre
  cy.get('button.profile_update').click();
  
  // Attendre la réponse AJAX
  cy.waitForAjax();
  
  // Vérifier le message de succès
  cy.get('#success_message').should('be.visible');
});

// Commande pour supprimer le compte
Cypress.Commands.add('deleteAccount', () => {
  cy.visitCinetech('profile');
  
  // Cliquer sur supprimer le compte
  cy.get('#delete_account_button').click();
  cy.get('#confirmation_message').should('be.visible');
  
  // Confirmer la suppression
  cy.get('#confirm_delete').click();
  
  // Vérifier la redirection
  cy.url().should('include', 'home');
});

// Commande pour vérifier la structure responsive
Cypress.Commands.add('checkResponsive', () => {
  // Desktop
  cy.viewport(1280, 720);
  cy.get('nav.navbar').should('be.visible');
  cy.get('.burger-menu').should('not.be.visible');
  
  // Tablette
  cy.viewport(768, 1024);
  cy.get('.burger-menu').should('be.visible');
  
  // Mobile
  cy.viewport(375, 667);
  cy.get('.burger-menu').should('be.visible');
  cy.get('.burger-menu').click();
  cy.get('nav.navbar').should('be.visible');
});

// Commande pour intercepter les requêtes API
Cypress.Commands.add('interceptApi', (method = 'GET', url = '**/api/**', response = null) => {
  if (response) {
    cy.intercept(method, url, response).as('apiCall');
  } else {
    cy.intercept(method, url).as('apiCall');
  }
});

// Commande pour attendre une requête API spécifique
Cypress.Commands.add('waitForApi', (alias = 'apiCall') => {
  cy.wait(`@${alias}`);
});

// Commande pour vérifier les messages d'erreur
Cypress.Commands.add('checkErrorMessage', (expectedMessage) => {
  cy.get('.error, .error-message, .alert-danger').should('be.visible');
  if (expectedMessage) {
    cy.get('.error, .error-message, .alert-danger').should('contain', expectedMessage);
  }
});

// Commande pour vérifier les messages de succès
Cypress.Commands.add('checkSuccessMessage', (expectedMessage) => {
  cy.get('.success, .success-message, .alert-success').should('be.visible');
  if (expectedMessage) {
    cy.get('.success, .success-message, .alert-success').should('contain', expectedMessage);
  }
});

// Commande pour nettoyer les données de test
Cypress.Commands.add('cleanTestData', () => {
  // Cette commande peut être utilisée pour nettoyer les données de test
  // Vous pouvez l'implémenter selon vos besoins spécifiques
  cy.log('Nettoyage des données de test...');
  
  // Exemple : supprimer les utilisateurs de test via API
  // cy.request('DELETE', '/api/test-users');
});

// Commande pour créer des données de test
Cypress.Commands.add('seedTestData', () => {
  // Cette commande peut être utilisée pour créer des données de test
  cy.log('Création des données de test...');
  
  // Exemple : créer des utilisateurs de test via API
  // cy.request('POST', '/api/test-users', testUserData);
});

// Commande pour simuler des conditions réseau lentes
Cypress.Commands.add('simulateSlowNetwork', () => {
  cy.intercept('**/*', (req) => {
    req.reply((res) => {
      // Ajouter un délai de 1 seconde
      return new Promise((resolve) => {
        setTimeout(() => resolve(res), 1000);
      });
    });
  });
});

// Commande pour vérifier les performances de base
Cypress.Commands.add('checkPerformance', (maxLoadTime = 3000) => {
  const startTime = Date.now();
  
  cy.window().then(() => {
    const loadTime = Date.now() - startTime;
    expect(loadTime).to.be.lessThan(maxLoadTime);
    cy.log(`Temps de chargement: ${loadTime}ms`);
  });
});

//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
