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
     */
    private function emailExists($email, $mockPdo)
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

    public function testEmailExistsReturnsFalseWhenEmailNotFound()
    {
        // Test 2: Email inexistant - doit retourner false
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
        
        $result = $this->emailExists($email, $this->mockPdo);
        
        $this->assertFalse($result, "La fonction devrait retourner false pour un email inexistant");
        fwrite(STDOUT, "Test 1 succès : Inscription réussie. Vous pouvez vous connecter");
    }

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

        $result = $this->emailExists($email, $this->mockPdo);

        // Assertion
        $this->assertTrue($result, "La fonction devrait retourner true pour un email existant");
        fwrite(STDOUT, "\nTest 2 : erreur : Cet email est déjà utilisé");
    }

    public function testEmailExistsHandlesDatabaseError()
    {
        // Test 3: Gestion d'erreur de base de données - doit retourner false
        $email = 'test@example.com';
        
        // Simuler une erreur PDO
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Database connection failed'));
        
        $result = $this->emailExists($email, $this->mockPdo);
        
        $this->assertFalse($result, "La fonction devrait retourner false en cas d'erreur de base de données");
        fwrite(STDOUT, "\nTest 3 erreur: Erreur lors de l'exécution de la requête");
    }

    // public function testEmailExistsWithEmptyEmail()
    // {
    //     // Test 4: Email vide - doit retourner false
    //     $email = '';
        
    //     $this->mockPdo->expects($this->once())
    //         ->method('prepare')
    //         ->with('SELECT * FROM users WHERE user_email = :user_email')
    //         ->willReturn($this->mockStmt);
        
    //     $this->mockStmt->expects($this->once())
    //         ->method('execute')
    //         ->with(['user_email' => $email]);
        
    //     $this->mockStmt->expects($this->once())
    //         ->method('fetch')
    //         ->willReturn(false);
        
    //     $result = $this->emailExists($email, $this->mockPdo, $this->mockStmt);
        
    //     $this->assertFalse($result, "La fonction devrait retourner false pour un email vide");
    // }

    // public function testEmailExistsWithValidEmailFormat()
    // {
    //     // Test 5: Email avec format valide mais inexistant
    //     $email = 'valid.email@domain.com';
        
    //     $this->mockPdo->expects($this->once())
    //         ->method('prepare')
    //         ->with('SELECT * FROM users WHERE user_email = :user_email')
    //         ->willReturn($this->mockStmt);
        
    //     $this->mockStmt->expects($this->once())
    //         ->method('execute')
    //         ->with(['user_email' => $email]);
        
    //     $this->mockStmt->expects($this->once())
    //         ->method('fetch')
    //         ->willReturn(false);
        
    //     $result = $this->emailExists($email, $this->mockPdo, $this->mockStmt);
        
    //     $this->assertFalse($result, "La fonction devrait retourner false pour un email valide mais inexistant");
    // }
}