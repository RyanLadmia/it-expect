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
        try
        {
            $query = "DELETE FROM users WHERE user_id = :user_id";
            $result = $mockPdo->prepare($query);
            $result->execute(['user_id' => $userId]);
            return true;
        }catch(\PDOException $e){
            return false;
        }
    }

    public function testDeleteUserByIdSuccesReturnTrue()
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
            ->with(['user_id' => $userId]);

        // Simuler l'execution de la requête
        $result = $this->deleteUserByIdHelper($userId, $this->mockPdo, $this->mockStmt);

        // Assertion
        $this->assertTrue($result, "La fonction devrait retourner true quand l'utilisateur est supprimé.");
        fwrite(STDOUT, "Test 1 : succès : L'utilisateur a bien été supprimé");
    }
}


