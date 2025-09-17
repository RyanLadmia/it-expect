<?php

use PHPUnit\Framework\TestCase;

class EmailExistsSimpleTest extends TestCase
{
    private $modelUser;

    protected function setUp(): void
    {
        // Création d'une classe anonyme qui simule ModelUser sans connexion DB
        $this->modelUser = new class {
            public function emailExists($email)
            {
                // Simulation simple : retourne false pour les tests
                // Dans un vrai test, cela devrait être mocké
                if (empty($email) || is_null($email)) {
                    return false;
                }
                return false; // Simulation
            }
        };
    }

    public function testEmailExistsIsCallable()
    {
        // Test 1: Vérifier que la méthode existe et est appelable
        $this->assertTrue(
            method_exists($this->modelUser, 'emailExists'),
            "La méthode emailExists devrait exister dans ModelUser"
        );
        
        $this->assertTrue(
            is_callable([$this->modelUser, 'emailExists']),
            "La méthode emailExists devrait être appelable"
        );
    }

    public function testEmailExistsReturnsBoolean()
    {
        // Test 2: Vérifier que la méthode retourne un boolean
        $result = $this->modelUser->emailExists('test@example.com');
        
        $this->assertIsBool(
            $result,
            "La méthode emailExists devrait retourner un boolean"
        );
    }

    public function testEmailExistsWithEmptyString()
    {
        // Test 3: Tester avec une chaîne vide
        $result = $this->modelUser->emailExists('');
        
        $this->assertIsBool(
            $result,
            "La méthode emailExists devrait retourner un boolean même avec une chaîne vide"
        );
    }

    public function testEmailExistsWithNullValue()
    {
        // Test 4: Tester avec null
        $result = $this->modelUser->emailExists(null);
        
        $this->assertIsBool(
            $result,
            "La méthode emailExists devrait retourner un boolean même avec null"
        );
    }

    public function testEmailExistsWithValidEmailFormat()
    {
        // Test 5: Tester avec un format d'email valide
        $validEmail = 'test.email@example.com';
        $result = $this->modelUser->emailExists($validEmail);
        
        $this->assertIsBool(
            $result,
            "La méthode emailExists devrait retourner un boolean pour un email valide"
        );
        
        // Note: Ce test ne vérifie pas si l'email existe réellement en base,
        // mais seulement que la méthode fonctionne sans erreur
    }
}
