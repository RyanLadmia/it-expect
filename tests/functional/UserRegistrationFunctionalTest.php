<?php

use PHPUnit\Framework\TestCase;

// Inclure les classes nécessaires pour le test fonctionnel
require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../model/ModelUser.php';

/**
 * TEST FONCTIONNEL : Inscription d'utilisateur
 * 
 * Ce test vérifie le comportement complet de l'inscription utilisateur
 * avec interaction réelle avec la base de données et toutes les dépendances.
 * 
 * Contrairement aux tests unitaires, ce test :
 * - Utilise la vraie base de données
 * - Teste le processus complet d'inscription
 * - Vérifie les effets de bord (données persistées)
 * - Teste les interactions entre méthodes (register + emailExists)
 */
class UserRegistrationFunctionalTest extends TestCase
{
    private $modelUser;
    private $testUsersToCleanup = [];

    protected function setUp(): void
    {
        // Utiliser la vraie classe ModelUser avec vraie DB
        $this->modelUser = new ModelUser();
    }

    protected function tearDown(): void
    {
        // Nettoyer les utilisateurs de test créés
        foreach ($this->testUsersToCleanup as $email) {
            $this->cleanupTestUser($email);
        }
    }

    /**
     * TEST FONCTIONNEL 1 : Inscription réussie d'un nouvel utilisateur
     * Vérifie tout le processus d'inscription avec vraie DB
     */
    public function testSuccessfulUserRegistration()
    {
        // ARRANGE : Préparer les données de test
        $firstname = 'Jean';
        $lastname = 'Dupont';
        $email = 'jean.dupont.test@example.com';
        $password = password_hash('motdepasse123', PASSWORD_DEFAULT);
        
        // Marquer pour nettoyage
        $this->testUsersToCleanup[] = $email;

        // ACT : Exécuter l'inscription
        $result = $this->modelUser->register($firstname, $lastname, $email, $password);

        // ASSERT : Vérifier le résultat
        $this->assertEquals('Inscription réussie', $result);
        
        // ASSERT FONCTIONNEL : Vérifier que l'utilisateur existe vraiment en DB
        $this->assertTrue($this->modelUser->emailExists($email));
        
        // ASSERT FONCTIONNEL : Vérifier les données persistées
        $this->assertUserExistsInDatabase($email, $firstname, $lastname);
        
        fwrite(STDOUT, "Test 1 : Inscription réussie - Utilisateur créé en base");
    }

    /**
     * TEST FONCTIONNEL 2 : Tentative d'inscription avec email existant
     * Teste la validation métier complète
     */
    public function testRegistrationWithExistingEmail()
    {
        // ARRANGE : Créer d'abord un utilisateur
        $firstname = 'Marie';
        $lastname = 'Martin';
        $email = 'marie.martin.test@example.com';
        $password = password_hash('motdepasse123', PASSWORD_DEFAULT);
        
        $this->testUsersToCleanup[] = $email;
        
        // Première inscription (doit réussir)
        $firstResult = $this->modelUser->register($firstname, $lastname, $email, $password);
        $this->assertEquals('Inscription réussie', $firstResult);

        // ACT : Tentative de deuxième inscription avec même email
        $secondResult = $this->modelUser->register('Pierre', 'Durand', $email, $password);

        // ASSERT : Vérifier le rejet
        $this->assertEquals('Cet email est déjà utilisé.', $secondResult);
        
        // ASSERT FONCTIONNEL : Vérifier qu'un seul utilisateur existe
        $this->assertOnlyOneUserWithEmail($email);
        
        fwrite(STDOUT, "\nTest 2 : Email existant - Rejet correct et DB cohérente");
    }

    /**
     * TEST FONCTIONNEL 3 : Inscription avec données spéciales
     * Teste la robustesse du processus complet
     */
    public function testRegistrationWithSpecialCharacters()
    {
        // ARRANGE : Données avec caractères spéciaux
        $firstname = "Jean-François";
        $lastname = "O'Connor";
        $email = 'jean.francois.test@example.com';
        $password = password_hash('M0t&Passe!@#', PASSWORD_DEFAULT);
        
        $this->testUsersToCleanup[] = $email;

        // ACT : Inscription
        $result = $this->modelUser->register($firstname, $lastname, $email, $password);

        // ASSERT : Vérifier le succès
        $this->assertEquals('Inscription réussie', $result);
        
        // ASSERT FONCTIONNEL : Vérifier la persistance des caractères spéciaux
        $userData = $this->getUserDataFromDatabase($email);
        $this->assertEquals($firstname, $userData['user_firstname']);
        $this->assertEquals($lastname, $userData['user_lastname']);
        
        fwrite(STDOUT, "\nTest 3 : Caractères spéciaux - Persistance correcte en DB");
    }

    /**
     * TEST FONCTIONNEL 4 : Vérification de la structure des données créées
     * Teste l'intégrité des données en base
     */
    public function testRegistrationDataIntegrity()
    {
        // ARRANGE
        $firstname = 'Alice';
        $lastname = 'Wonderland';
        $email = 'alice.test@example.com';
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $this->testUsersToCleanup[] = $email;

        // ACT
        $result = $this->modelUser->register($firstname, $lastname, $email, $password);

        // ASSERT : Inscription réussie
        $this->assertEquals('Inscription réussie', $result);

        // ASSERT FONCTIONNEL : Vérifier tous les champs créés
        $userData = $this->getUserDataFromDatabase($email);
        
        $this->assertNotNull($userData['user_id']);
        $this->assertEquals($firstname, $userData['user_firstname']);
        $this->assertEquals($lastname, $userData['user_lastname']);
        $this->assertEquals($email, $userData['user_email']);
        $this->assertEquals($password, $userData['user_password']);
        $this->assertNotNull($userData['created_at']);
        
        // Vérifier le format de la date
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $userData['created_at']);
        
        fwrite(STDOUT, "\nTest 4 : Intégrité des données - Tous les champs corrects en DB");
    }

    /**
     * TEST FONCTIONNEL 5 : Test de performance et volume
     * Teste le comportement avec plusieurs inscriptions
     */
    public function testMultipleRegistrationsPerformance()
    {
        $startTime = microtime(true);
        $successCount = 0;
        
        // ARRANGE & ACT : Créer 5 utilisateurs différents
        for ($i = 1; $i <= 5; $i++) {
            $email = "user{$i}.test@example.com";
            $this->testUsersToCleanup[] = $email;
            
            $result = $this->modelUser->register(
                "User{$i}",
                "Test{$i}",
                $email,
                password_hash("password{$i}", PASSWORD_DEFAULT)
            );
            
            if ($result === 'Inscription réussie') {
                $successCount++;
            }
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // ASSERT : Toutes les inscriptions réussies
        $this->assertEquals(5, $successCount);
        
        // ASSERT FONCTIONNEL : Performance acceptable (< 2 secondes)
        $this->assertLessThan(2.0, $executionTime);
        
        // ASSERT FONCTIONNEL : Tous les utilisateurs existent en DB
        for ($i = 1; $i <= 5; $i++) {
            $this->assertTrue($this->modelUser->emailExists("user{$i}.test@example.com"));
        }
        
        fwrite(STDOUT, "\nTest 5 : Performance - 5 inscriptions en {$executionTime}s");
    }

    // ========== MÉTHODES UTILITAIRES POUR TESTS FONCTIONNELS ==========

    /**
     * Vérifier qu'un utilisateur existe en base avec les bonnes données
     */
    private function assertUserExistsInDatabase($email, $expectedFirstname, $expectedLastname)
    {
        $userData = $this->getUserDataFromDatabase($email);
        
        $this->assertNotNull($userData, "L'utilisateur devrait exister en base");
        $this->assertEquals($expectedFirstname, $userData['user_firstname']);
        $this->assertEquals($expectedLastname, $userData['user_lastname']);
        $this->assertEquals($email, $userData['user_email']);
    }

    /**
     * Vérifier qu'un seul utilisateur existe avec cet email
     */
    private function assertOnlyOneUserWithEmail($email)
    {
        $conn = new Connection();
        $pdo = $conn->connectionDB();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE user_email = :email");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(1, $result['count'], "Il ne devrait y avoir qu'un seul utilisateur avec cet email");
    }

    /**
     * Récupérer les données utilisateur depuis la base
     */
    private function getUserDataFromDatabase($email)
    {
        $conn = new Connection();
        $pdo = $conn->connectionDB();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_email = :email");
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Nettoyer un utilisateur de test
     */
    private function cleanupTestUser($email)
    {
        try {
            $conn = new Connection();
            $pdo = $conn->connectionDB();
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_email = :email");
            $stmt->execute(['email' => $email]);
        } catch (Exception $e) {
            // Ignorer les erreurs de nettoyage
        }
    }
}
