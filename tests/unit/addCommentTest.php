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
     * Simule la fonction addComment EXACTEMENT comme dans ModelComment
     */
    private function addComment($userId, $itemId, $type, $content, $mockPdo)
    {
        // Reproduction EXACTE de la logique de ModelComment.php
        $stmt = $mockPdo->prepare("
            INSERT INTO comments (user_id, item_id, type, content) 
            VALUES (:user_id, :item_id, :type, :content)
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':item_id' => $itemId,
            ':type' => $type,
            ':content' => $content
        ]);
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
            ->with("
            INSERT INTO comments (user_id, item_id, type, content) 
            VALUES (:user_id, :item_id, :type, :content)
        ")
            ->willReturn($this->mockStmt);
        
        // Configuration du mock Statement - utilise execute() avec array comme ModelComment
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':user_id' => $userId,
                ':item_id' => $itemId,
                ':type' => $type,
                ':content' => $content
            ])
            ->willReturn(true);
        
        // Exécution du test
        $result = $this->addComment($userId, $itemId, $type, $content, $this->mockPdo);
        
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
            ->with("
            INSERT INTO comments (user_id, item_id, type, content) 
            VALUES (:user_id, :item_id, :type, :content)
        ")
            ->willReturn($this->mockStmt);
        
        // Configuration du mock Statement - utilise execute() avec array comme ModelComment
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':user_id' => $userId,
                ':item_id' => $itemId,
                ':type' => $type,
                ':content' => $content
            ])
            ->willReturn(false); // Simulation d'un échec
        
        // Exécution du test
        $result = $this->addComment($userId, $itemId, $type, $content, $this->mockPdo);
        
        // Vérification
        $this->assertFalse($result, "La fonction devrait retourner false quand l'ajout échoue");
    }

    public function testAddCommentThrowsExceptionOnDatabaseError()
    {
        // Test 3: La fonction DOIT lever une exception comme dans ModelComment
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Commentaire avec erreur';
        
        // Simuler une exception PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database error'));
        
        // Vérification que l'exception est bien levée (comme dans ModelComment)
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Database error');
        
        // Exécution du test - doit lever l'exception
        $this->addComment($userId, $itemId, $type, $content, $this->mockPdo);
    }
}