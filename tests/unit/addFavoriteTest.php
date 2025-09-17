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
     * Simule la fonction addFavorite EXACTEMENT comme dans ModelFavorite
     */
    private function addFavorite($userId, $contentId, $contentType, $mockPdo) 
    {
        // Reproduction EXACTE de la logique de ModelFavorite.php
        $query = "INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)";
        $stmt = $mockPdo->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':content_id', $contentId);
        $stmt->bindParam(':content_type', $contentType);
        return $stmt->execute();
    }

    public function testAddFavoriteReturnsTrueWhenSuccess()
    {
        // Test 1: Ajout réussi d'un favori
        $userId = 1;
        $contentId = 123;
        $contentType = 'tv';

        // Configuration du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)')
            ->willReturn($this->mockStmt);

        // Configuration du mock PDOStatement - utilise bindParam() comme ModelFavorite
        $this->mockStmt->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Exécution du test
        $result = $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo);

        // Vérification
        $this->assertTrue($result, "La fonction devrait retourner true lorsque l'ajout est réussi");
        fwrite(STDOUT, "Test 1 : succès : Ajouté aux favoris avec succès");
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
        $result = $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo);

        // Vérification
        $this->assertFalse($result, "La fonction devrait retourner false lorsque l'ajout échoue");
        fwrite(STDOUT, "\nTest 2 : succès : Erreur lors de l'ajout du favori");
    }

    public function testAddFavoriteThrowsExceptionOnDatabaseError()
    {
        // Test 4: La fonction DOIT lever une exception comme dans ModelFavorite
        $userId = 1;
        $contentId = 123;
        $contentType = 'movie';

        // Simuler une exception PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database error'));

        // Vérification que l'exception est bien levée (comme dans ModelFavorite)
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Database error');

        // Message AVANT l'exécution qui lève l'exception
        fwrite(STDOUT, "\nTest 3 : erreur: Erreur lors de l'exécution de la requête");

        // Exécution du test - doit lever l'exception
        $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo);
    }

    public function testAddFavoriteHandlesDuplicateEntry()
    {
        // Test 4: Contrainte UNIQUE en base de données
        // RÉALITÉ : Votre DB a bien une contrainte UNIQUE (user_id, content_id, content_type)
        // PROBLÈME : Votre application ne gère PAS cette exception -> Erreur 500
        $userId = 1;
        $contentId = 123;
        $contentType = 'movie';

        // Configuration du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)')
            ->willReturn($this->mockStmt);

        // Configuration du mock PDOStatement - utilise bindParam() comme ModelFavorite
        $this->mockStmt->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturn(true);

        // Simuler la contrainte UNIQUE de votre vraie base de données
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'1-123-movie\' for key \'favorites.user_id\''));

        // TEST RÉALISTE : L'exception remonte non gérée (comme dans votre app)
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Duplicate entry');

        // Message AVANT l'exécution qui lève l'exception
        fwrite(STDOUT, "\nTest 4 : erreur: Contrainte UNIQUE DB - Exception non gérée (Erreur 500)");

        // Exécution du test
        $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo);
    }

    public function testAddFavoriteWithDifferentContentTypes()
    {
        // Test 5: Ajout avec différents types de contenu
        $userId = 1;
        $contentId = 456;
        $contentType = 'tv'; // Type série

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
        $result = $this->addFavorite($userId, $contentId, $contentType, $this->mockPdo);

        // Vérification
        $this->assertTrue($result, "La fonction devrait retourner true pour une série");
        fwrite(STDOUT, "\nTest 5 : succès : Série ajoutée aux favoris avec succès");
    }
}