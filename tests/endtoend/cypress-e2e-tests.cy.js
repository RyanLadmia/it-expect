/**
 * TESTS END-TO-END CYPRESS - APPLICATION CINETECH
 * 
 * Ces tests vérifient le comportement complet de l'application
 * du point de vue de l'utilisateur final, en testant :
 * - Navigation entre les pages
 * - Authentification complète
 * - Gestion des favoris
 * - Système de commentaires
 * - Interactions AJAX
 * - Responsive design
 * 
 * Configuration requise :
 * - Serveur local actif (MAMP/XAMPP)
 * - Base de données configurée
 * - npm install cypress --save-dev
 * - npx cypress open
 */

describe('Cinetech - Tests End-to-End', () => {
  const baseUrl = 'http://localhost/it-expect'; // Ajustez selon votre configuration
  const testUser = {
    firstname: 'Test',
    lastname: 'E2E',
    email: 'test.e2e@cypress.com',
    password: 'TestPassword123!'
  };

  beforeEach(() => {
    // Nettoyer les cookies et le localStorage avant chaque test
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  /**
   * SUITE 1 : NAVIGATION ET STRUCTURE DE L'APPLICATION
   */
  describe('Navigation et Structure', () => {
    it('devrait charger la page d\'accueil correctement', () => {
      cy.visit(baseUrl);
      
      // Vérifier le titre et les éléments principaux
      cy.title().should('contain', 'Cinetech');
      cy.get('header').should('be.visible');
      cy.get('.logo img').should('be.visible').and('have.attr', 'alt', 'Logo de Cinetech');
      cy.get('nav.navbar').should('be.visible');
      cy.get('footer').should('be.visible').and('contain', 'Cinetech Ryan Ladmia 2024');
    });

    it('devrait naviguer vers toutes les pages principales', () => {
      cy.visit(baseUrl);

      // Navigation vers Films
      cy.get('nav a').contains('Films').click();
      cy.url().should('include', '/movie');
      cy.title().should('contain', 'Films');

      // Navigation vers Séries
      cy.get('nav a').contains('Séries').click();
      cy.url().should('include', '/serie');
      cy.title().should('contain', 'Séries');

      // Navigation vers Accueil via logo
      cy.get('.logo a').click();
      cy.url().should('eq', baseUrl + '/');
    });

    it('devrait afficher le menu responsive', () => {
      cy.visit(baseUrl);
      cy.viewport(768, 1024); // Tablette

      // Vérifier que le menu burger est visible
      cy.get('.burger-menu').should('be.visible');
      
      // Cliquer sur le menu burger
      cy.get('.burger-menu').click();
      
      // Vérifier que la navigation est accessible
      cy.get('nav.navbar').should('be.visible');
    });
  });

  /**
   * SUITE 2 : AUTHENTIFICATION COMPLÈTE
   */
  describe('Authentification', () => {
    it('devrait permettre l\'inscription d\'un nouvel utilisateur', () => {
      cy.visit(`${baseUrl}?r=login`);

      // Vérifier que nous sommes sur la page de connexion
      cy.get('h1').should('contain', 'Connexion');

      // Remplir le formulaire d'inscription
      cy.get('input[name="firstname"]').type(testUser.firstname);
      cy.get('input[name="lastname"]').type(testUser.lastname);
      cy.get('input[name="email"]').type(testUser.email);
      cy.get('input[name="password"]').type(testUser.password);
      cy.get('input[name="confirm_password"]').type(testUser.password);

      // Sélectionner le type de formulaire inscription
      cy.get('input[name="form_type"][value="register"]').check();

      // Soumettre le formulaire
      cy.get('button[type="submit"]').contains('S\'inscrire').click();

      // Vérifier le message de succès ou la redirection
      cy.get('.success-message, .message').should('be.visible');
    });

    it('devrait permettre la connexion avec des identifiants valides', () => {
      cy.visit(`${baseUrl}?r=login`);

      // Basculer vers le formulaire de connexion
      cy.get('input[name="form_type"][value="login"]').check();

      // Remplir les champs de connexion
      cy.get('input[name="email"]').type(testUser.email);
      cy.get('input[name="password"]').type(testUser.password);

      // Soumettre le formulaire
      cy.get('button[type="submit"]').contains('Se connecter').click();

      // Vérifier la redirection vers l'accueil
      cy.url().should('include', 'home');
      
      // Vérifier que le bouton de déconnexion est visible
      cy.get('button.deco').should('be.visible').and('contain', 'Se déconnecter');
    });

    it('devrait afficher une erreur avec des identifiants invalides', () => {
      cy.visit(`${baseUrl}?r=login`);

      // Basculer vers le formulaire de connexion
      cy.get('input[name="form_type"][value="login"]').check();

      // Utiliser des identifiants incorrects
      cy.get('input[name="email"]').type('wrong@email.com');
      cy.get('input[name="password"]').type('wrongpassword');

      // Soumettre le formulaire
      cy.get('button[type="submit"]').contains('Se connecter').click();

      // Vérifier le message d'erreur
      cy.get('.error, .error-message').should('be.visible');
    });

    it('devrait permettre la déconnexion', () => {
      // D'abord se connecter
      cy.login(testUser.email, testUser.password); // Commande personnalisée

      // Cliquer sur déconnexion
      cy.get('button.deco').contains('Se déconnecter').click();

      // Vérifier la redirection vers login
      cy.url().should('include', 'login');
      
      // Vérifier que le bouton de connexion est visible
      cy.get('button.co a').should('contain', 'Se Connecter');
    });
  });

  /**
   * SUITE 3 : GESTION DU PROFIL UTILISATEUR
   */
  describe('Profil Utilisateur', () => {
    beforeEach(() => {
      cy.login(testUser.email, testUser.password);
    });

    it('devrait afficher les informations du profil', () => {
      cy.visit(`${baseUrl}?r=profile`);

      // Vérifier les éléments du profil
      cy.get('h2').should('contain', `Bienvenue, ${testUser.firstname}`);
      cy.get('#user_firstname').should('contain', testUser.firstname);
      cy.get('#user_lastname').should('contain', testUser.lastname);
      cy.get('#user_email').should('contain', testUser.email);
    });

    it('devrait permettre la modification du prénom', () => {
      cy.visit(`${baseUrl}?r=profile`);

      // Cliquer sur modifier
      cy.get('#edit_button').click();
      
      // Le formulaire de modification devrait apparaître
      cy.get('#update_form').should('be.visible');

      // Sélectionner le champ prénom
      cy.get('#field').select('user_firstname');
      
      // Entrer une nouvelle valeur
      const newFirstname = 'TestModifié';
      cy.get('#new_value').type(newFirstname);

      // Soumettre le formulaire
      cy.get('button.profile_update').click();

      // Vérifier le message de succès
      cy.get('#success_message').should('be.visible');
      
      // Vérifier que la valeur a été mise à jour
      cy.get('#user_firstname').should('contain', newFirstname);
    });

    it('devrait permettre la suppression du compte', () => {
      cy.visit(`${baseUrl}?r=profile`);

      // Cliquer sur supprimer le compte
      cy.get('#delete_account_button').click();

      // Le message de confirmation devrait apparaître
      cy.get('#confirmation_message').should('be.visible');

      // Confirmer la suppression
      cy.get('#confirm_delete').click();

      // Vérifier la redirection vers l'accueil
      cy.url().should('include', 'home');
    });
  });

  /**
   * SUITE 4 : SYSTÈME DE FAVORIS
   */
  describe('Gestion des Favoris', () => {
    beforeEach(() => {
      cy.login(testUser.email, testUser.password);
    });

    it('devrait afficher la page des favoris', () => {
      cy.visit(`${baseUrl}?r=favorite`);

      cy.get('h1').should('contain', 'Mes Favoris');
      
      // Soit il y a des favoris, soit le message "pas de favoris"
      cy.get('body').should('contain.oneOf', ['Vous n\'avez pas encore de favoris', 'favoritesList']);
    });

    it('devrait permettre d\'ajouter un favori depuis la page détail', () => {
      // Aller sur une page de détail (exemple avec un film)
      cy.visit(`${baseUrl}?r=detail&id=12345&type=movie`);

      // Chercher le bouton d'ajout aux favoris
      cy.get('button, .favorite-btn').contains(/ajouter|favori/i).first().click();

      // Vérifier le message de succès ou la mise à jour de l'interface
      cy.get('.success, .message').should('be.visible');
    });

    it('devrait permettre de supprimer un favori', () => {
      cy.visit(`${baseUrl}?r=favorite`);

      // Si il y a des favoris
      cy.get('#favoritesList .favorite-item').then($items => {
        if ($items.length > 0) {
          // Cliquer sur supprimer le premier favori
          cy.get('.remove-favorite-btn').first().click();
          
          // Confirmer la suppression
          cy.get('.confirm-remove').click();
          
          // Vérifier le message de succès
          cy.get('.success-message').should('be.visible');
        }
      });
    });
  });

  /**
   * SUITE 5 : SYSTÈME DE COMMENTAIRES
   */
  describe('Système de Commentaires', () => {
    beforeEach(() => {
      cy.login(testUser.email, testUser.password);
    });

    it('devrait permettre d\'ajouter un commentaire', () => {
      cy.visit(`${baseUrl}?r=detail&id=12345&type=movie`);

      const commentText = 'Excellent film ! Test E2E avec Cypress.';
      
      // Remplir le formulaire de commentaire
      cy.get('textarea[name="content"]').type(commentText);
      
      // Soumettre le commentaire
      cy.get('button[type="submit"]').contains(/commenter|ajouter/i).click();

      // Vérifier que le commentaire apparaît
      cy.get('.comment').should('contain', commentText);
    });

    it('devrait permettre de supprimer un commentaire', () => {
      cy.visit(`${baseUrl}?r=detail&id=12345&type=movie`);

      // Chercher les commentaires de l'utilisateur connecté
      cy.get('.comment').then($comments => {
        if ($comments.length > 0) {
          // Cliquer sur supprimer le premier commentaire
          cy.get('.delete-comment').first().click();
          
          // Confirmer la suppression si nécessaire
          cy.get('body').then($body => {
            if ($body.find('.confirm-delete').length > 0) {
              cy.get('.confirm-delete').click();
            }
          });

          // Vérifier que le commentaire a été supprimé
          cy.get('.success, .message').should('be.visible');
        }
      });
    });
  });

  /**
   * SUITE 6 : FONCTIONNALITÉS AVANCÉES
   */
  describe('Fonctionnalités Avancées', () => {
    it('devrait permettre la recherche de contenus', () => {
      cy.visit(baseUrl);

      const searchTerm = 'Avengers';
      
      // Utiliser la barre de recherche
      cy.get('#search').type(searchTerm);
      
      // Vérifier que les suggestions apparaissent
      cy.get('#suggestion').should('be.visible');
      
      // Attendre les résultats AJAX
      cy.wait(1000);
      
      // Vérifier qu'il y a des résultats
      cy.get('#suggestion').should('not.be.empty');
    });

    it('devrait gérer les erreurs 404', () => {
      cy.visit(`${baseUrl}?r=nonexistent`, { failOnStatusCode: false });

      // Vérifier que la page 404 est affichée
      cy.get('body').should('contain', '404');
    });

    it('devrait fonctionner correctement sur mobile', () => {
      cy.viewport('iphone-x');
      cy.visit(baseUrl);

      // Vérifier que le menu burger est visible
      cy.get('.burger-menu').should('be.visible');
      
      // Tester la navigation mobile
      cy.get('.burger-menu').click();
      cy.get('nav.navbar').should('be.visible');
      
      // Vérifier que le contenu s'adapte
      cy.get('main').should('be.visible');
    });
  });

  /**
   * SUITE 7 : TESTS DE PERFORMANCE ET ACCESSIBILITÉ
   */
  describe('Performance et Accessibilité', () => {
    it('devrait charger rapidement', () => {
      const startTime = Date.now();
      
      cy.visit(baseUrl);
      
      cy.window().then(() => {
        const loadTime = Date.now() - startTime;
        expect(loadTime).to.be.lessThan(3000); // Moins de 3 secondes
      });
    });

    it('devrait avoir des éléments accessibles', () => {
      cy.visit(baseUrl);

      // Vérifier les attributs d'accessibilité
      cy.get('img').should('have.attr', 'alt');
      cy.get('input').should('have.attr', 'placeholder').or('have.attr', 'aria-label');
      cy.get('button').should('not.be.empty');
    });
  });
});

/**
 * COMMANDES PERSONNALISÉES CYPRESS
 */
Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    cy.visit(`${Cypress.env('baseUrl') || 'http://localhost/it-expect'}?r=login`);
    
    cy.get('input[name="form_type"][value="login"]').check();
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    cy.get('button[type="submit"]').contains('Se connecter').click();
    
    cy.url().should('include', 'home');
  });
});

/**
 * CONFIGURATION CYPRESS (cypress.config.js)
 * 
 * const { defineConfig } = require('cypress');
 * 
 * module.exports = defineConfig({
 *   e2e: {
 *     baseUrl: 'http://localhost/it-expect',
 *     viewportWidth: 1280,
 *     viewportHeight: 720,
 *     video: true,
 *     screenshotOnRunFailure: true,
 *     defaultCommandTimeout: 10000,
 *     requestTimeout: 10000,
 *     responseTimeout: 10000,
 *     setupNodeEvents(on, config) {
 *       // implement node event listeners here
 *     },
 *   },
 * });
 */
