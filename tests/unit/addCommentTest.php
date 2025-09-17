<?php

use PHPUnit\Framework\TestCase;

class AddCommentTest extends TestCase
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
     * Simule la fonction addComment sans dépendre de ModelComment
     */
    private function addComment($userId, $itemId, $type, $content, $mockPdo, $mockStmt)
    {
        try {
            $query = "INSERT INTO comments (user_id, item_id, type, content) VALUES (:user_id, :item_id, :type, :content)";
            $stmt = $mockPdo->prepare($query);
            
            // Simulation des bindParam
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':item_id', $itemId);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':content', $content);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function testAddCommentReturnsTrueWhenSuccess()
    {
        // Test 1: Ajout réussi d'un commentaire
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Super film !';
        
        // Configuration du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO comments (user_id, item_id, type, content) VALUES (:user_id, :item_id, :type, :content)')
            ->willReturn($this->mockStmt);
        
        // Configuration du mock Statement
        $this->mockStmt->expects($this->exactly(4))
            ->method('bindParam')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        // Exécution du test
        $result = $this->addComment($userId, $itemId, $type, $content, $this->mockPdo, $this->mockStmt);
        
        // Vérification
        $this->assertTrue($result, "La fonction devrait retourner true quand l'ajout réussit");
    }

    public function testAddCommentReturnsFalseWhenFailure()
    {
        // Test 2: Échec de l'ajout d'un commentaire
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Commentaire échoué';
        
        // Configuration du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO comments (user_id, item_id, type, content) VALUES (:user_id, :item_id, :type, :content)')
            ->willReturn($this->mockStmt);
        
        // Configuration du mock Statement
        $this->mockStmt->expects($this->exactly(4))
            ->method('bindParam')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false); // Simulation d'un échec
        
        // Exécution du test
        $result = $this->addComment($userId, $itemId, $type, $content, $this->mockPdo, $this->mockStmt);
        
        // Vérification
        $this->assertFalse($result, "La fonction devrait retourner false quand l'ajout échoue");
    }

    public function testAddCommentHandlesException()
    {
        // Test 3: Gestion d'exception
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Commentaire avec erreur';
        
        // Simuler une exception PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database error'));
        
        // Exécution du test
        $result = $this->addComment($userId, $itemId, $type, $content, $this->mockPdo, $this->mockStmt);
        
        // Vérification
        $this->assertFalse($result, "La fonction devrait retourner false en cas d'exception");
    }
}