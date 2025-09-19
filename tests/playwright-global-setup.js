/**
 * Configuration globale Playwright - Setup
 * Exécuté une seule fois avant tous les tests
 */

async function globalSetup(config) {
  console.log('🚀 Démarrage de la configuration globale Playwright...');
  
  // Ici vous pouvez :
  // 1. Démarrer des services externes
  // 2. Initialiser la base de données de test
  // 3. Créer des données de test
  // 4. Configurer l'authentification globale
  
  // Exemple : Créer des utilisateurs de test
  try {
    console.log('📝 Création des utilisateurs de test...');
    
    // Vous pourriez faire des requêtes HTTP pour créer des données de test
    // ou exécuter des scripts SQL
    
    // Exemple de création d'utilisateur via API
    // const response = await fetch(`${config.use.baseURL}/api/test-users`, {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({
    //     email: 'test.playwright@example.com',
    //     password: 'TestPassword123!',
    //     firstname: 'Test',
    //     lastname: 'Playwright'
    //   })
    // });
    
    console.log('✅ Configuration globale terminée avec succès');
    
  } catch (error) {
    console.error('❌ Erreur lors de la configuration globale:', error);
    throw error;
  }
}

module.exports = globalSetup;
