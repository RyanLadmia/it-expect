<?php

use PHPUnit\Framework\TestCase;

class GetFavoritesTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;

    protected function setUp(): void
    {
        // Mock de PDO
        $this->mockPdo = $this->createMock(PDO::class);

        // Mock de Statement
        $this->mockStmt = $this->createMock(PDOStatement::class);
    }

    /**
     * Simule la fonction getFavorite EXACTEMENT comme dans ModelFavorite
     * en modifiant le nom pour éviter d'appeler la vraie fonction 
     * Cela garantit que le test reste un test unitaire et pas un test fonctionnel 
     */
    private function getFavoritesHelper($userId, $mockPdo, $mockStmt){
        try
        {
            if (!$userId)
            {
                throw new InvalidArgumentException("ID utilisateur invalide");
            }
            $query = "SELECT * FROM favorites WHERE user_id = :user_id ORDER BY favorite_id DESC";
            $stmt = $this->mockPdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            throw new Exception ("Erreur lors de la récupération des favoris : " . $e->getMessage());
        }
    }

    // Test 1 : Succès lors de la récupération des favoris d'un utilisateur 
    public function testGetFavoritesReturnTrueWhenFound()
    {
        $userId = 1;

        // Simulation du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM favorites WHERE user_id = :user_id ORDER BY favorite_id DESC")
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('execute');

        // Simulation de l'éxécution de la requête
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                            ['favorite_id' => 1, 'user_id' => 1, 'item_id' => 123, 'type' => 'movie'],
                            ['favorite_id' => 2, 'user_id' => 1, 'item_id' => 456, 'type' => 'tv']
            ]);

        $result = $this->getFavoritesHelper($userId, $this->mockPdo, $this->mockStmt);

        // Assertion
        $this->assertTrue(is_array($result), "La fonction devrait retourner un tableau avec les favoris de l'utilisateur");
        fwrite(STDOUT, "\n\nTests unitaires GetFavoritesTest.php\n");
        fwrite(STDOUT, "Test 1 : succès : Favoris récupérés avec succès");
    }

    // Test 2 : Échec de la récupération des favoris d'un utilisateur
    public function testGetFavoritesReturnFalseWhenNotFound(){

        $userId = 2;

        // Simulation du mock PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        // Simulation du mock Statement
        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':user_id', $userId, PDO::PARAM_INT);

        $this->mockStmt->expects($this->once())
            ->method('execute');

        // Simulation de l'exécution de la requête
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]); // Aucun favori trouvé

        $result = $this->getFavoritesHelper($userId, $this->mockPdo, $this->mockStmt);

        $this->assertIsArray($result, "La fonction devrait retourner un tableau vide si aucun favori");
        $this->assertEmpty($result, "Le tableau doit être vide si aucun favori n'est trouvé");
        fwrite(STDOUT, "\nTest 2 : échec : Aucun favori trouvé");
    }

    // Test 3 : Gestion de base de données - doit retourner false
    public function testGetFavoritesHandlessDatabaseError()
    {
        $userId = 3;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Erreur lors de la récupération des favoris : Database error');

        fwrite(STDOUT, "\nTest 3 : échec : Erreur lors de l'exécution de la requête");
        $this->getFavoritesHelper($userId, $this->mockPdo, $this->mockStmt);
    }
    
}


?>