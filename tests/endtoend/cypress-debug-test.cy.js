/**
 * TEST CYPRESS DE DÉBOGAGE - CINETECH
 * 
 * Ce test simple vous aide à comprendre comment Cypress fonctionne
 * avec votre application réelle. Chaque étape est commentée.
 */

describe('🔍 Cypress Debug - Comprendre les résultats', () => {
  beforeEach(() => {
    // 1. Nettoyer avant chaque test
    cy.clearCookies();
    cy.clearLocalStorage();
    
    // 2. Message de débogage
    cy.log('🚀 Début du test - serveur sur localhost/it-expect');
  });

  it('🏠 Test 1 : Charger la page d\'accueil et analyser', () => {
    // ÉTAPE 1 : Naviguer vers votre site
    cy.log('📍 Navigation vers la page d\'accueil...');
    cy.visit('/');
    
    // ÉTAPE 2 : Vérifier le titre (devrait passer)
    cy.log('📄 Vérification du titre de la page...');
    cy.title().should('contain', 'Cinetech');
    
    // ÉTAPE 3 : Vérifier la structure de base
    cy.log('🏗️ Vérification de la structure HTML...');
    cy.get('body').should('be.visible');
    cy.get('header').should('exist');
    
    // ÉTAPE 4 : Attendre et analyser le contenu
    cy.log('⏳ Attente du chargement complet...');
    cy.wait(1000); // Laisser le temps au contenu de se charger
    
    // ÉTAPE 5 : Capturer des informations de débogage
    cy.get('body').then(($body) => {
      cy.log('📊 Contenu de la page chargé');
      cy.log('🎯 Nombre d\'éléments header:', $body.find('header').length);
      cy.log('🎯 Nombre d\'éléments nav:', $body.find('nav').length);
      cy.log('🎯 Nombre de liens:', $body.find('a').length);
    });
  });

  it('🔍 Test 2 : Explorer la navigation disponible', () => {
    cy.visit('/');
    
    // Analyser tous les liens disponibles
    cy.log('🔗 Analyse des liens de navigation...');
    
    cy.get('a').then(($links) => {
      cy.log(`📊 Trouvé ${$links.length} liens sur la page`);
      
      // Afficher les 5 premiers liens pour débogage
      $links.slice(0, 5).each((index, link) => {
        cy.log(`🔗 Lien ${index + 1}: ${link.href} - Texte: "${link.innerText}"`);
      });
    });
    
    // Tester la navigation vers Films (si le lien existe)
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Films")').length > 0) {
        cy.log('✅ Lien "Films" trouvé - Test de navigation...');
        cy.get('a').contains('Films').click();
        cy.url().should('include', 'movie');
        cy.log('✅ Navigation vers Films réussie !');
      } else {
        cy.log('⚠️ Lien "Films" non trouvé - Vérifiez le texte exact');
      }
    });
  });

  it('🎯 Test 3 : Tester les éléments interactifs', () => {
    cy.visit('/');
    
    // Chercher une barre de recherche
    cy.log('🔍 Recherche d\'une barre de recherche...');
    cy.get('body').then(($body) => {
      if ($body.find('input[type="text"]').length > 0) {
        cy.log('✅ Champ de recherche trouvé !');
        cy.get('input[type="text"]').first().type('test');
        cy.log('✅ Saisie dans le champ de recherche réussie');
      } else {
        cy.log('ℹ️ Aucun champ de recherche trouvé sur cette page');
      }
    });
    
    // Chercher des boutons
    cy.log('🔘 Recherche de boutons...');
    cy.get('button').then(($buttons) => {
      cy.log(`📊 Trouvé ${$buttons.length} boutons`);
      
      if ($buttons.length > 0) {
        $buttons.each((index, button) => {
          cy.log(`🔘 Bouton ${index + 1}: "${button.innerText || button.textContent}"`);
        });
      }
    });
  });

  it('❌ Test 4 : Exemple d\'échec volontaire (pour comprendre)', () => {
    cy.visit('/');
    
    cy.log('⚠️ Ce test va échouer volontairement pour vous montrer');
    
    // Test qui va échouer - élément qui n'existe pas
    cy.get('.element-qui-nexiste-pas', { timeout: 2000 })
      .should('be.visible');
    
    // Cette ligne ne sera jamais atteinte à cause de l'échec ci-dessus
    cy.log('❌ Cette ligne ne s\'exécutera jamais');
  });

  it('🔧 Test 5 : Gestion d\'erreur avec condition', () => {
    cy.visit('/');
    
    cy.log('🛠️ Test avec gestion d\'erreur conditionnelle');
    
    // Approche plus robuste - vérifier l'existence avant d'agir
    cy.get('body').then(($body) => {
      if ($body.find('.login-button').length > 0) {
        cy.log('✅ Bouton de connexion trouvé');
        cy.get('.login-button').should('be.visible');
      } else {
        cy.log('ℹ️ Bouton de connexion non trouvé - c\'est OK');
      }
    });
    
    // Test conditionnel pour la navigation
    cy.get('body').then(($body) => {
      const hasProfileLink = $body.find('a:contains("Profil")').length > 0;
      const hasLoginLink = $body.find('a:contains("Connexion")').length > 0;
      
      if (hasProfileLink) {
        cy.log('👤 Utilisateur connecté - lien Profil visible');
      } else if (hasLoginLink) {
        cy.log('🔑 Utilisateur non connecté - lien Connexion visible');
      } else {
        cy.log('❓ État de connexion incertain');
      }
    });
  });

  it('📊 Test 6 : Rapport détaillé de la page', () => {
    cy.visit('/');
    
    cy.log('📋 Génération d\'un rapport détaillé...');
    
    // Analyser la page complètement
    cy.get('body').then(($body) => {
      const report = {
        title: document.title,
        url: window.location.href,
        headers: $body.find('h1, h2, h3').length,
        links: $body.find('a').length,
        buttons: $body.find('button').length,
        forms: $body.find('form').length,
        inputs: $body.find('input').length,
        images: $body.find('img').length
      };
      
      cy.log('📊 RAPPORT DE LA PAGE :');
      cy.log(`📄 Titre: ${report.title}`);
      cy.log(`🌐 URL: ${report.url}`);
      cy.log(`📝 Titres (h1-h3): ${report.headers}`);
      cy.log(`🔗 Liens: ${report.links}`);
      cy.log(`🔘 Boutons: ${report.buttons}`);
      cy.log(`📋 Formulaires: ${report.forms}`);
      cy.log(`📝 Champs de saisie: ${report.inputs}`);
      cy.log(`🖼️ Images: ${report.images}`);
    });
  });
});

/**
 * COMMENT UTILISER CE TEST :
 * 
 * 1. Lancez : npx cypress open
 * 2. Sélectionnez : cypress-debug-test.cy.js
 * 3. Observez chaque test s'exécuter
 * 4. Regardez les logs dans le panneau de droite
 * 5. Le Test 4 échouera volontairement - c'est normal !
 * 
 * POINTS D'ATTENTION :
 * - Test 1 : Devrait toujours passer (tests de base)
 * - Test 2 : Peut échouer si les liens ont des noms différents
 * - Test 3 : Analyse les éléments interactifs disponibles
 * - Test 4 : ÉCHOUE VOLONTAIREMENT pour montrer les erreurs
 * - Test 5 : Montre comment éviter les erreurs
 * - Test 6 : Donne un rapport complet de votre page
 */
