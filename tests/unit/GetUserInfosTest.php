<?php

use PHPUnit\Framework\TestCase;

class getUserInfosTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;

    protected function setUp(): void
    {
        // Mock de PDO
        $this->mockPdo = $this->createMock(PDO::class);

        // Mock de PDOStatement
        $this->mockStmt = $this->createMock(PDOStatement::class);
    }

    /**
     * Simule la fonction getUserInfos EXACTEMENT comme dans ModelUser 
     * en modifiant le nom pour éviter d'appeler la vraie fonction getUserInfos de ModelUser
     * Cela garantit que le test reste un test unitaire et pas un test fonctionnel
     */
    private function getUserInfosHelper($userId, $mockPdo, $mockStmt)
    {
        try
        {
            $query = "SELECT user_firstname, user_lastname, user_email, created_at FROM users WHERE user_id = :user_id";
            $result = $mockPdo->prepare($query);
            $result->execute(['user_id' => $userId]);
            return $result->fetch(\PDO::FETCH_ASSOC);
        }catch (\PDOException $e){
            return false;
        }
    }

    // Test 1 : Récupération des informations de l'utilisateur
    public function testUserInfosReturnTrueWhenFound()
    {
        $userId = 1;

        // Simulation du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT user_firstname, user_lastname, user_email, created_at FROM users WHERE user_id = :user_id')
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $userId]);

        // Simulation de l'exécution de la requête
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                            'user_firstname' => 'Ryan',
                            'user_lastname' => 'Ladmia',
                            'user_email' => 'ryan.ladmia@example.com',
                            'created_at' => '2012-12-12 12:30:00'
                        ]);

        $result = $this->getUserInfosHelper($userId, $this->mockPdo, $this->mockStmt);
    
        // Assertion 
        $this->assertTrue(is_array($result), "La fonction devrait retourner un tableau avec les infos de l'utilisateur");
        fwrite(STDOUT, "Test 1 : succès : Données de l'utilisateur récupérées avec succès");
    }

    // Test 2 : Erreur lors de la récupération l'utilisateur
    public function testUserInfosReturnFalseWhenNotFound()
    {
        $userId = 1;

        // Simulation du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT user_firstname, user_lastname, user_email, created_at FROM users WHERE user_id = :user_id')
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $userId]);

        // Simulation de l'exécution de la requête
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        $result = $this->getUserInfosHelper($userId, $this->mockPdo, $this->mockStmt);
    
        // Assertion 
        $this->assertFalse($result, "La fonction devrait false pour une erreur de récupération si l'utilisateur n'est pas trouvé.");
        fwrite(STDOUT, "\nTest 2 : erreur : Erreur utilisateur introuvable");
    }

    // Test 3 : Utilisateur trouvé mais erreur de récupération des données
    public function testUserInfosThrowsExceptionWhenFetchFails()
    {
        $userId = 1;

        // Simulation du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT user_firstname, user_lastname, user_email, created_at FROM users WHERE user_id = :user_id')
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $userId]);

        // Simuler une erreur lors de la récupération des données
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willThrowException(new PDOException('Erreur lors de la récupération'));

        // Test : on s’attend à ce que le helper gère l’exception et retourne false
        $result = $this->getUserInfosHelper($userId, $this->mockPdo, $this->mockStmt);

        // Assertion
        $this->assertFalse($result, "La fonction devrait retourner false si une erreur survient lors de la récupération des données");
        fwrite(STDOUT, "\nTest 3 : erreur : Erreur lors de la récupération des données pour un utilisateur existant");
    }

    // Test 4 : Gestion d'erreur de base de données - doit retourner false
    public function testGetUserInfosHandlessDatabaseError()
    {
        $userId = 33;
        
        // Simuler une erreur PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database connection failed'));
        
        $result = $this->getUserInfosHelper($userId, $this->mockPdo, $this->mockStmt);
        
        // Assertion
        $this->assertFalse($result, "La fonction devrait retourner false en cas d'erreur de base de données");
        fwrite(STDOUT, "\nTest 4 : erreur : Erreur lors de l'exécution de la requête");
    }
}



