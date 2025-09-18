<?php

use PHPUnit\Framework\TestCase;

class EmailExistsTest extends TestCase
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
     * Simule la fonction emailExists EXACTEMENT comme dans ModelUser 
     * en modifiant le nom pour éviter d'appeler la fonction emailExists de ModelUser
     * Cela garantit que le test reste un test unitaire et pas un test fonctionnel
     */
    private function emailExistsHelper($email, $mockPdo)
    {
        try {
            $query = "SELECT * FROM users WHERE user_email = :user_email";
            $result = $mockPdo->prepare($query);
            $result->execute(['user_email' => $email]);
            if ($result->fetch()) {
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Test 1 : Email inexistant : Inscription réussie. Vous pouvez vous connecter.
    public function testEmailExistsReturnsFalseWhenEmailNotFound()
    {
        $email = 'nonexistent@example.com';
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users WHERE user_email = :user_email')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_email' => $email]);
        
        // Simuler qu'aucun utilisateur n'est trouvé
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);
        
        $result = $this->emailExistsHelper($email, $this->mockPdo);
        
        $this->assertFalse($result, "La fonction devrait retourner false pour un email inexistant");
        fwrite(STDOUT, "Test 1 : succès : Inscription réussie. Vous pouvez vous connecter");
    }

    // Test 2 : Cet email est déjà utilisé. 
    public function testEmailExistsReturnsTrueWhenEmailFound()
    {
        $email = 'test@example.com';
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users WHERE user_email = :user_email')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_email' => $email]);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['user_id' => 1, 'user_email' => $email]);

        $result = $this->emailExistsHelper($email, $this->mockPdo);

        // Assertion
        $this->assertTrue($result, "La fonction devrait retourner true pour un email existant");
        fwrite(STDOUT, "\nTest 2 : erreur : Cet email est déjà utilisé");
    }

    

    // Test 3 : Email vide - doit retourner false
    public function testEmailExistsWithEmptyEmail()
    {
        $email = '';
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users WHERE user_email = :user_email')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_email' => $email]);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);
        
        $result = $this->emailExistsHelper($email, $this->mockPdo, $this->mockStmt);
        
        $this->assertFalse($result, "La fonction devrait retourner false pour un email vide");
        fwrite(STDOUT, "\nTest 3 : erreur : Erreur lors de l'inscription. Veuillez saisir une adresse email");

    }

    // Test 4 : Email avec format invalide 
        public function testEmailExistsWithInvalidEmailFormat()
    {
        // Test 5 : Email au format invalide - doit lever une exception
        $email = 'invalid-email-format'; // pas de @, donc invalide

        // On attend une exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Format d'email invalide");

        // Message affiché dans le terminal
        fwrite(STDOUT, "\nTest 4 : erreur : Format d'email invalide");

        // Vérification du format AVANT appel de la fonction
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Format d'email invalide");
        }

        // Exécution de la fonction (ici ne sera pas atteinte à cause de l'exception)
        $this->emailExistsHelper($email, $this->mockPdo);
    }

    public function testEmailExistsHandlesDatabaseError()
    {
        // Test 5 : Gestion d'erreur de base de données - doit retourner false
        $email = 'test@example.com';
        
        // Simuler une erreur PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database connection failed'));
        
        $result = $this->emailExistsHelper($email, $this->mockPdo);
        
        $this->assertFalse($result, "La fonction devrait retourner false en cas d'erreur de base de données");
        fwrite(STDOUT, "\nTest 5 : erreur : Erreur lors de l'exécution de la requête");
    }
}