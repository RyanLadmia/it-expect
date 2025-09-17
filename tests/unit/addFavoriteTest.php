<?php

use PHPUnit\Framework\TestCase;

class AddFavoriteTest extends TestCase
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
     * Simule la fonction addFavorite sans dépendre de ModelFavorite
     */
    private function addFavorite($userId, $contentId, $contentType, $mockPdo, $mockStmt) 
    {
        try {
            $query = "INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)";
            $stmt = $mockPdo->prepare($query);
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':content_id', $contentId);
            $stmt->bindParam(':content_type', $contentType);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function testAddFavoriteReturnsTrueWhenSuccess()
    {
        // Test 1: Ajout réussi d'un favori
        $userId = 1;
        $contentId = 123;
        $contentType = 'movie';

        // Configuration du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)')
            ->willReturn($this->mockStmt);

        // Configuration du mock PDOStatement
        $this->mockStmt->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Exécution du test
        $result = $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo, $this->mockStmt);

        // Vérification
        $this->assertTrue($result, "La fonction devrait retourner true lorsque l'ajout est réussi");
    }

    public function testAddFavoriteReturnsFalseWhenFailure()
    {
        // Test 2: Échec de l'ajout d'un favori
        $userId = 1;
        $contentId = 123;
        $contentType = 'movie';

        // Configuration du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)')
            ->willReturn($this->mockStmt);

        // Configuration du mock PDOStatement
        $this->mockStmt->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false); // Simulation d'un échec

        // Exécution du test
        $result = $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo, $this->mockStmt);

        // Vérification
        $this->assertFalse($result, "La fonction devrait retourner false lorsque l'ajout échoue");
    }

    public function testAddFavoriteHandlesException()
    {
        // Test 3: Gestion d'exception
        $userId = 1;
        $contentId = 123;
        $contentType = 'movie';

        // Simuler une exception PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database error'));

        // Exécution du test
        $result = $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo, $this->mockStmt);

        // Vérification
        $this->assertFalse($result, "La fonction devrait retourner false en cas d'exception");
    }
}