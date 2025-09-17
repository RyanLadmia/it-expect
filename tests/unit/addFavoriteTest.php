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
     * Simule la fonction favoriteExists EXCATEMENT comme dans ModelFavorite
     */
    private function favoriteExists($userId, $contentId, $contentType, $mockPdo) {
        try{
            $query = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND content_id = :content_id AND content_type = :content_type";
            $stmt = $mockPdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindParam(':content_type', $contentType, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification du favori : " . $e->getMessage());
        }
    }

    /**
     * Simule la fonction addFavorite EXACTEMENT comme dans ModelFavorite
     */
    private function addFavorite($userId, $contentId, $contentType, $mockPdo) 
    {
        try{
            if (!$userId || !$contentId || !in_array($contentType, ['movie', 'tv'])) {
                throw new InvalidArgumentException("Paramètres invalides pour l'ajout aux favoris");
            }

            if ($this->favoriteExists($userId, $contentId, $contentType, $mockPdo)) {
                throw new Exception("Cet élément existe déjà dans vos favoris");
            }

            $query = "INSERT INTO favorites (user_id, content_id, content_type) VALUES (:user_id, :content_id, :content_type)";
            $stmt = $mockPdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindParam(':content_type', $contentType, PDO::PARAM_STR);

            if (!$stmt->execute()){
                throw new Exception("Échec de l'ajout aux favoris");
            }

            return true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                throw new Exception("Cet élément existe déjà dans vos favoris");
            }
            throw new Exception("Erreur lors de l'ajout aux favoris : " . $e->getMessage());
            }
        
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

    
}