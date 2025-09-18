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
    private function getFavoriteHelper($userId, $mockPdo, $mockStmt){
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
}


?>