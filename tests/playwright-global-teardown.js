/**
 * Configuration globale Playwright - Teardown
 * Exécuté une seule fois après tous les tests
 */

async function globalTeardown(config) {
  console.log('🧹 Démarrage du nettoyage global Playwright...');
  
  try {
    // Ici vous pouvez :
    // 1. Nettoyer les données de test créées
    // 2. Arrêter des services externes
    // 3. Supprimer les fichiers temporaires
    // 4. Nettoyer la base de données de test
    
    console.log('🗑️ Nettoyage des données de test...');
    
    // Exemple : Supprimer les utilisateurs de test créés
    // await fetch(`${config.use.baseURL}/api/cleanup-test-data`, {
    //   method: 'DELETE',
    //   headers: { 'Authorization': 'Bearer test-token' }
    // });
    
    // Exemple : Nettoyer la base de données
    // const mysql = require('mysql2/promise');
    // const connection = await mysql.createConnection({
    //   host: 'localhost',
    //   user: 'test_user',
    //   password: 'test_password',
    //   database: 'cinetech_test'
    // });
    // 
    // await connection.execute('DELETE FROM users WHERE email LIKE "%test%"');
    // await connection.execute('DELETE FROM comments WHERE user_id IN (SELECT user_id FROM users WHERE email LIKE "%test%")');
    // await connection.end();
    
    console.log('✅ Nettoyage global terminé avec succès');
    
  } catch (error) {
    console.error('❌ Erreur lors du nettoyage global:', error);
    // Ne pas faire échouer les tests si le nettoyage échoue
    console.warn('⚠️ Continuons malgré l\'erreur de nettoyage...');
  }
}

module.exports = globalTeardown;
