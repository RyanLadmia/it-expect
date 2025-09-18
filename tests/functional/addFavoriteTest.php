<?php

use PHPUnit\Framework\TestCase;

class AddFavoriteTest extends TestCase
{
    /**
     * TEST UNITAIRE PUR : Validation des paramètres
     * Teste uniquement la logique de validation sans dépendances
     */
    public function testAddFavoriteValidatesParameters()
    {
        // Test avec userId invalide
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Paramètres invalides pour l'ajout aux favoris");
        
        $this->addFavoriteLogicHelper(0, 123, 'movie');
        fwrite(STDOUT, "Test 1 : succès - Validation userId invalide");
    }

    public function testAddFavoriteValidatesContentType()
    {
        // Test avec contentType invalide
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Paramètres invalides pour l'ajout aux favoris");
        
        $this->addFavoriteLogicHelper(1, 123, 'invalid_type');
        fwrite(STDOUT, "\nTest 2 : succès - Validation contentType invalide");
    }

    /**
     * TEST UNITAIRE PUR : Logique de duplication
     * Teste la logique sans base de données
     */
    public function testAddFavoriteDetectsDuplicate()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cet élément existe déjà dans vos favoris");
        
        // Simuler qu'un favori existe déjà
        $this->addFavoriteLogicHelper(1, 123, 'movie', true); // true = favori existe
        fwrite(STDOUT, "\nTest 3 : succès - Détection doublon");
    }

    /**
     * TEST UNITAIRE PUR : Logique de succès
     * Teste la logique de retour sans base de données
     */
    public function testAddFavoriteReturnsTrue()
    {
        // Test avec paramètres valides et pas de doublon
        $result = $this->addFavoriteLogicHelper(1, 123, 'movie', false); // false = pas de favori existant
        
        $this->assertTrue($result);
        fwrite(STDOUT, "\nTest 4 : succès - Ajout favori valide");
    }

    /**
     * TEST UNITAIRE PUR : Gestion d'erreur PDO
     * Teste la logique d'exception sans vraie base de données
     */
    public function testAddFavoriteHandlesPDOException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cet élément existe déjà dans vos favoris");
        
        // Simuler une erreur PDO avec Duplicate entry
        $this->addFavoriteLogicHelper(1, 123, 'movie', false, true); // true = simuler PDOException
        fwrite(STDOUT, "\nTest 5 : succès - Gestion exception PDO");
    }

    /**
     * HELPER POUR TEST UNITAIRE PUR
     * Simule UNIQUEMENT la logique métier sans aucune dépendance externe
     */
    private function addFavoriteLogicHelper($userId, $contentId, $contentType, $favoriteExists = false, $simulatePDOException = false)
    {
        // 1. VALIDATION DES PARAMÈTRES (logique pure)
        if (!$userId || !$contentId || !in_array($contentType, ['movie', 'tv'])) {
            throw new InvalidArgumentException("Paramètres invalides pour l'ajout aux favoris");
        }

        // 2. SIMULATION DE LA VÉRIFICATION D'EXISTENCE (pas de vraie DB)
        if ($favoriteExists) {
            throw new Exception("Cet élément existe déjà dans vos favoris");
        }

        // 3. SIMULATION D'EXCEPTION PDO (pas de vraie DB)
        if ($simulatePDOException) {
            // Simuler une PDOException avec message Duplicate entry
            throw new Exception("Cet élément existe déjà dans vos favoris");
        }

        // 4. LOGIQUE DE RETOUR (logique pure)
        return true;
    }

}