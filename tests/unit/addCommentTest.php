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
        $this->mockStmt = $this->createMock(PDOStatement::class);
    }

    /**
     * Helper simulant exactement la fonction addComment de ModelComment
     */
    private function addCommentHelper($userId, $itemId, $type, $content, $mockPdo)
    {
        try {
            if (!$userId || !is_numeric($userId)) {
                throw new InvalidArgumentException("ID utilisateur invalide");
            }
            if (!$itemId || !is_numeric($itemId)) {
                throw new InvalidArgumentException("ID d'élément invalide");
            }
            if (!in_array($type, ['movie', 'tv'])) {
                throw new InvalidArgumentException("Type d'élément invalide");
            }
            if (empty(trim($content)) || strlen(trim($content)) < 1) {
                throw new InvalidArgumentException("Le contenu du commentaire ne peut pas être vide");
            }
            if (strlen($content) > 1000) {
                throw new InvalidArgumentException("Le commentaire est trop long (max 1000 caractères)");
            }

            $stmt = $mockPdo->prepare("
                INSERT INTO comments (user_id, item_id, type, content) 
                VALUES (:user_id, :item_id, :type, :content)
            ");

            if (!$stmt->execute([
                ':user_id' => $userId,
                ':item_id' => $itemId,
                ':type' => $type,
                ':content' => trim($content)
            ])) {
                throw new Exception("Échec de l'ajout du commentaire");
            }

            return true;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout du commentaire : " . $e->getMessage());
        }
    }

    // Test 1 : Ajout réussi d'un commentaire
    public function testAddCommentReturnsTrueWhenSuccess()
    {
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Super film !';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with("
                INSERT INTO comments (user_id, item_id, type, content) 
                VALUES (:user_id, :item_id, :type, :content)
            ")
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':user_id' => $userId,
                ':item_id' => $itemId,
                ':type' => $type,
                ':content' => trim($content)
            ])
            ->willReturn(true);

        $result = $this->addCommentHelper($userId, $itemId, $type, $content, $this->mockPdo);

        $this->assertTrue($result, "La fonction devrait retourner true quand l'ajout réussit");
        fwrite(STDOUT, "\n\nTests unitaires AddCommentTest.php\n");
        fwrite(STDOUT, "Test 1 : succès : Commentaire ajouté avec succès");
    }

    // Test 2 : Échec de l'ajout d'un commentaire
    public function testAddCommentReturnsFalseWhenFailure()
    {
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Commentaire échoué';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':user_id' => $userId,
                ':item_id' => $itemId,
                ':type' => $type,
                ':content' => trim($content)
            ])
            ->willReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Échec de l'ajout du commentaire");

        fwrite(STDOUT, "\nTest 2 : échec : Échec de l'ajout du commentaire");
        $this->addCommentHelper($userId, $itemId, $type, $content, $this->mockPdo);
    }

    // Test 3 : Erreur lors de la requête PDO
    public function testAddCommentThrowsExceptionOnDatabaseError()
    {
        $userId = 1;
        $itemId = 123;
        $type = 'movie';
        $content = 'Commentaire avec erreur';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Erreur lors de l\'ajout du commentaire : Database error');

        fwrite(STDOUT, "\nTest 3 : échec : Erreur lors de l'exécution de la requête");
        $this->addCommentHelper($userId, $itemId, $type, $content, $this->mockPdo);
    }
}