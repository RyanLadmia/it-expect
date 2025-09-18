<?php

use Dotenv\Dotenv;

require 'vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ModelUser
{
    private $connexion;

    public function __construct()
    {
        $conn = new Connection();
        $this->connexion = $conn->connectionDB();
        $this->connexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    }

    public function register($firstname, $lastname, $email, $password)
    {
        try {
            if($this->emailExists($email)) {
                return 'Cet email est déjà utilisé.';
            } else {
                $requete = $this->connexion->prepare("INSERT INTO users (user_id, user_firstname, user_lastname, user_email, user_password, created_at) VALUES (null, ?, ?, ?, ?, NOW())");
                $requete->execute([ $firstname, $lastname, $email, $password ]);
                return 'Inscription réussie';
            }
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'inscription : " . $e->getMessage());
        }
    }

    public function login($email, $password)
    {
        try {
            $query = "SELECT * FROM users WHERE user_email = :user_email";
            $result = $this->connexion->prepare($query);
            $result->execute(['user_email' => $email]);
            $user = $result->fetch(\PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['user_password'])) {
                // Démarrer la session si pas encore démarrée
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['user'] = $user['user_id'];
                $_SESSION['user_firstname'] = $user['user_firstname'];
                $_SESSION['user_lastname'] = $user['user_lastname'];
                $_SESSION['user_email'] = $user['user_email'];

                return $user;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la connexion : " . $e->getMessage());
        }
    }

    /**
     * Déconnexion de l'utilisateur
     * Suit le même principe que login() avec gestion de session
     */
    public function logout()
    {
        try {
            // Démarrer la session si pas encore démarrée
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Vérifier qu'une session utilisateur existe
            if (!isset($_SESSION['user'])) {
                return false; // Cohérent avec le retour de login()
            }
            
            // Sauvegarder les infos utilisateur avant déconnexion
            $userInfo = [
                'user_id' => $_SESSION['user'] ?? null,
                'user_email' => $_SESSION['user_email'] ?? null
            ];
            
            // Nettoyer complètement la session
            session_unset();
            session_destroy();
            
            // Démarrer une nouvelle session propre
            session_start();
            session_regenerate_id(true);
            
            return $userInfo; // Cohérent avec le retour de login()
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la déconnexion : " . $e->getMessage());
        }
    }

    public function emailExists($email)
    {
        try {
            $query = "SELECT * FROM users WHERE user_email = :user_email";
            $result = $this->connexion->prepare($query);
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


    public function getUserInfos($userId)
    {
        try
        {
            $query = "SELECT user_firstname, user_lastname, user_email, created_at FROM users WHERE user_id = :user_id";
            $result = $this->connexion->prepare($query);
            $result->execute(['user_id' => $userId]);
            return $result->fetch(\PDO::FETCH_ASSOC);
        }catch (\PDOException $e){
            return false;
        }
    }

    public function updateUserField($userId, $field, $value, $confirmPassword = null)
    {
        try {
            // Gestion des transformations spécifiques selon le champ
            if ($field === 'user_password') {
                // Validation et hashage du mot de passe
                if ($value !== $confirmPassword) {
                    return "Les mots de passe ne correspondent pas.";
                }
                $value = password_hash($value, PASSWORD_DEFAULT);
            } elseif ($field === 'user_firstname' || $field === 'user_lastname') {
                // Formatage du prénom et nom : première lettre en majuscule, reste en minuscule
                $value = ucwords(strtolower(htmlspecialchars($value)));
            } elseif ($field === 'user_email') {
                // Formatage de l'email en minuscule
                $value = strtolower(htmlspecialchars($value));
            } else {
                // Autres champs (si nécessaires) avec une simple protection
                $value = htmlspecialchars($value);
            }
    
            // Mise à jour de la base de données
            $query = "UPDATE users SET $field = ? WHERE user_id = ?";
            $result = $this->connexion->prepare($query);
            $result->execute([$value, $userId]);
    
            return true; // Mise à jour réussie
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function deleteUserById($userId)
    {
        try {
            $query = "DELETE FROM users WHERE user_id = :user_id";
            $stmt = $this->connexion->prepare($query);

            if (!$stmt->execute(['user_id' => $userId])) {
                return false; // si la requête échoue directement
            }

            // Vérifie si au moins une ligne a été supprimée
            if ($stmt->rowCount() === 0) {
                return false; // utilisateur trouvé mais aucune donnée supprimée
            }

            return true;
        } catch (\PDOException $e) {
            return false; 
        }
    }


    public function sendPasswordResetEmail($email)
    {
        try {
            // Vérification si l'email existe
            if (!$this->emailExists($email)) {
                return 'Cet email n\'existe pas.';
            }

            // Génération du token et de sa date d'expiration
            $resetToken = bin2hex(random_bytes(32));
            $resetTokenExpiration = new \DateTime();
            $resetTokenExpiration->modify('+2 hours');

            // Mise à jour des informations dans la base de données
            $query = "UPDATE users SET reset_token = :reset_token, reset_token_expiration = :reset_token_expiration WHERE user_email = :email";
            $stmt = $this->connexion->prepare($query);
            $stmt->execute([
                            ':reset_token' => $resetToken,
                            ':reset_token_expiration' => $resetTokenExpiration->format('Y-m-d H:i:s'),
                            ':email' => $email
                            ]);

            if ($stmt->rowCount() === 0) {
                throw new \Exception("Échec de la mise à jour du token. L'utilisateur avec cet email n'existe peut-être pas.");
            }

            // Création du lien de réinitialisation
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/cinetech/reset_password?token={$resetToken}";

            // Initialisation de PHPMailer
            $mail = new PHPMailer(true);

            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
            $mail->Port       = $_ENV['MAIL_PORT'];
            $mail->CharSet    = $_ENV['MAIL_CHARSET'];

            // Configuration de l'email
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body    = "
                <p>Bonjour,</p>
                <p>Nous avons reçu une demande de réinitialisation de votre mot de passe.</p>
                <p>Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci-dessous :</p>
                <a href='{$resetLink}'>Réinitialiser mon mot de passe</a>
                <p>Ce lien est valable pendant 2 heures.</p>
                <p>Si vous n'avez pas fait cette demande, veuillez ignorer cet email.</p>
                <p>Cordialement,<br>L'équipe Cinetech</p>
            ";

            // Envoi de l'email
            $mail->send();
            return 'Un email de réinitialisation a été envoyé.';
        } catch (Exception $e) {
            throw new \Exception("Erreur lors de l'envoi de l'email. Veuillez réessayer ultérieurement.");
        } catch (\PDOException $e) {
            throw new \Exception("Erreur de base de données. Veuillez réessayer ultérieurement.");
        }
    }

    public function verifyResetToken($token)
    {
        try {
            // Vérification du token
            $query = "SELECT user_id, reset_token, reset_token_expiration FROM users WHERE reset_token = :token";
            $stmt = $this->connexion->prepare($query);
            $stmt->execute([':token' => $token]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false;
            }
            
            // Vérification de l'expiration
            $expiration = new \DateTime($result['reset_token_expiration']);
            $now = new \DateTime();
            
            if ($now > $expiration) {
                return false;
            }
            
            return $result['user_id'];
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification du token. Veuillez réessayer ultérieurement.");
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la vérification du token. Veuillez réessayer ultérieurement.");
        }
    }

    public function updatePassword($token, $newPassword)
    {
        try {
            // Vérifier le token et récupérer l'ID utilisateur
            $userId = $this->verifyResetToken($token);
            if (!$userId) {
                return false; // Token invalide ou expiré
            }

            // Hacher le mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe et réinitialiser le token
            $query = "UPDATE users SET user_password = :password, reset_token = NULL, reset_token_expiration = NULL WHERE user_id = :user_id";
            $stmt = $this->connexion->prepare($query);
            $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => $userId
            ]);

            return true;
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour du mot de passe. Veuillez réessayer ultérieurement.");
        }
    }


}
    
