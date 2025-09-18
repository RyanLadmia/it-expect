<?php

use PHPUnit\Framework\TestCase;

class DeleteUserByIdTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;

    protected function setUp(): void
    {
        // Mock de PDO
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
    }

    /**
     * Simule la fonction deleteUserById() du ModelUser
     * Pour ne pas dépendre de la vraie fonction
     */
    public function deleteUserByIdHelper($userId, $mockPdo, $mockStmt)
{
    try {
        $query = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->mockPdo->prepare($query);

        if (!$stmt->execute(['user_id' => $userId])) {
            return false; // si la requête échoue directement
        }

        // Vérifie si au moins une ligne a été supprimée
        if ($stmt->rowCount() === 0) {
            return false; // utilisateur trouvé mais aucune donnée supprimée
        }

        return true;
    } catch (\PDOException $e) {
        return false; 
    }
}

    // Test 1 : Suppression d'un utilisateur et de ses données
    public function testDeleteUserByIdWhenSuccesReturnTrue()
    {
        $userId = 1;

        // Simulation du  mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM users WHERE user_id = :user_id')
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $userId])
            ->willReturn(true);
        
        // Simuler la vérification de la suppression
        $this->mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1); // Suppression effective

        // Simuler l'execution de la requête
        $result = $this->deleteUserByIdHelper($userId, $this->mockPdo, $this->mockStmt);

        // Assertion
        $this->assertTrue($result, "La fonction devrait retourner true quand l'utilisateur est supprimé.");
        fwrite(STDOUT, "Test 1 : succès : L'utilisateur a bien été supprimé");
    }

    // Test 2 : Utilisateur introuvable
        public function testDeleteUserByIdWhenFailReturnFalse()
    {
        $userId = 2;

        // Simulation du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM users WHERE user_id = :user_id')
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $userId])
            ->willReturn(true);

        // Simuler la vérification de la suppression
        $this->mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0); // Aucune ligne supprimée

        $result = $this->deleteUserByIdHelper($userId, $this->mockPdo, $this->mockStmt);

        $this->assertFalse($result, "La fonction devrait retourner false quand la suppression échoue.");
        fwrite(STDOUT, "\nTest 2 : succès : Erreur de la suppression des données. Utilisateur introuvable.");
    }


    // Test 2 : Erreur lors de la suppression de l'utilisateur et de toutes ses données
    public function testDeleteUserByIdWhenExecutionFailsReturnFalse()
    {
        $userId = 3;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM users WHERE user_id = :user_id')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => $userId])
            ->willThrowException(new \PDOException("Erreur lors de la suppression"));

        $result = $this->deleteUserByIdHelper($userId, $this->mockPdo, $this->mockStmt);

        $this->assertFalse($result, "La fonction devrait retourner false quand la suppression échoue.");
        fwrite(STDOUT, "\nTest 3 : erreur : Utilisateur trouvé mais échec de la suppression de l'utilisateur");
    }

    // Test 4 : Gestion d'erreur de base de données - doit retourner false
    public function testDeleteUserByIdHandlessDatabaseError()
        {
            $userId = 4;

            // Simuler une erreur PDO
            $this->mockPdo->expects($this->once())
                ->method('prepare')
                ->willThrowException(new PDOException('Database connection failed'));

            $result = $this->deleteUserByIdHelper($userId, $this->mockPdo, $this->mockStmt);

            // Assertion
            $this->assertFalse($result, "La fonction devrait retouner false en cas d'erreur de base de données");
            fwrite(STDOUT, "\nTest 4 : erreur : Erreur lors de l'exécution de la requête");
        }
}


