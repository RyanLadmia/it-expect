<?php

class UserController
{
    private $ModelUser;

    public function __construct()
    {
        $this->ModelUser = new ModelUser();
    }

    /**
     * Inscription avec gestion d'erreurs
     * @return array Résultat avec success et message
     */
    public function register($firstname, $lastname, $email, $password)
    {
        try {
            $result = $this->ModelUser->register($firstname, $lastname, $email, $password);
            
            if ($result === 'Inscription réussie') {
                return [
                    'success' => true,
                    'message' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Connexion avec gestion d'erreurs et session
     * @return array Résultat avec success et message
     */
    public function login($email, $password)
    {
        try {
            $result = $this->ModelUser->login($email, $password);
            
            if ($result) {
                // Démarrer la session si pas encore démarrée
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Créer la session utilisateur
                $_SESSION['user'] = $result['user_id'];
                $_SESSION['user_firstname'] = $result['user_firstname'];
                $_SESSION['user_lastname'] = $result['user_lastname'];
                $_SESSION['user_email'] = $result['user_email'];
                
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getUserInfos($userId)
    {
        return $this->ModelUser->getUserInfos($userId);
    }


    public function updateUserField($userId, $field, $value, $confirmPassword = null)
    {
        return $this->ModelUser->updateUserField($userId, $field, $value, $confirmPassword);
    }

    public function deleteUserById($userId)
    {
        return $this->ModelUser->deleteUserById($userId);
    }




    public function sendPasswordResetEmail($email)
    {
        return $this->ModelUser->sendPasswordResetEmail($email);
    }
    
    public function verifyResetToken($token)
    {
        return $this->ModelUser->verifyResetToken($token);
    }
    
    public function updatePassword($token, $newPassword)
    {
        return $this->ModelUser->updatePassword($token, $newPassword);
    }

    // ===== MÉTHODES POUR LES PAGES (GESTION LOGIQUE VUE) =====

    /**
     * Traiter le formulaire d'inscription depuis login.php
     */
    public function handleRegisterForm($postData) {
        $errors = [];
        
        // Validation des champs
        if (empty($postData['firstname'])) {
            $errors['firstname'] = 'Le prénom est requis';
        }
        if (empty($postData['lastname'])) {
            $errors['lastname'] = 'Le nom est requis';
        }
        if (empty($postData['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide';
        }
        if (empty($postData['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (strlen($postData['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        }
        if ($postData['password'] !== $postData['password_confirm']) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
        }

        // Si aucune erreur, procéder à l'inscription
        if (empty($errors)) {
            $firstname = ucwords(strtolower(htmlspecialchars($postData['firstname'])));
            $lastname = ucwords(strtolower(htmlspecialchars($postData['lastname'])));
            $email = strtolower(htmlspecialchars($postData['email']));
            $password = password_hash($postData['password'], PASSWORD_DEFAULT);

            $result = $this->register($firstname, $lastname, $email, $password);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Inscription réussie. Vous pouvez vous connecter.',
                    'switch_form' => true
                ];
            } else {
                $errors['email'] = $result['message'];
            }
        }

        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    /**
     * Traiter le formulaire de connexion depuis login.php
     */
    public function handleLoginForm($postData) {
        $errors = [];
        
        // Validation des champs
        if (empty($postData['email'])) {
            $errors['email'] = 'L\'email est requis';
        }
        if (empty($postData['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        }

        // Si aucune erreur, procéder à la connexion
        if (empty($errors)) {
            $email = strtolower(htmlspecialchars($postData['email']));
            $password = $postData['password'];

            $result = $this->login($email, $password);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'redirect' => 'home',
                    'user' => $result['user']
                ];
            } else {
                $errors['general'] = $result['message'];
            }
        }

        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    /**
     * Traiter la déconnexion
     */
    public function handleLogout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Déconnexion réussie',
            'redirect' => 'login'
        ];
    }

    /**
     * Traiter la demande de réinitialisation de mot de passe
     */
    public function handlePasswordResetRequest($postData) {
        $errors = [];
        
        if (empty($postData['email'])) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide';
        }

        if (empty($errors)) {
            $email = strtolower(htmlspecialchars($postData['email']));
            
            try {
                $result = $this->sendPasswordResetEmail($email);
                
                return [
                    'success' => true,
                    'message' => 'Email de réinitialisation envoyé'
                ];
            } catch (Exception $e) {
                $errors['general'] = 'Erreur lors de l\'envoi : ' . $e->getMessage();
            }
        }

        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    /**
     * Traiter la réinitialisation de mot de passe
     */
    public function handlePasswordReset($postData) {
        $errors = [];
        
        if (empty($postData['token'])) {
            $errors['general'] = 'Token manquant';
        }
        if (empty($postData['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (strlen($postData['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        }
        if ($postData['password'] !== $postData['password_confirm']) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
        }

        if (empty($errors)) {
            try {
                $result = $this->updatePassword($postData['token'], $postData['password']);
                
                return [
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès'
                ];
            } catch (Exception $e) {
                $errors['general'] = 'Erreur lors de la réinitialisation : ' . $e->getMessage();
            }
        }

        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    /**
     * Traiter les modifications de profil via AJAX
     */
    public function handleProfileUpdate($postData) {
        if (!isset($postData['field']) || !isset($postData['new_value'])) {
            return [
                'success' => false,
                'message' => 'Paramètres manquants'
            ];
        }

        $field = $postData['field'];
        $newValue = $postData['new_value'];
        $confirmPassword = $postData['confirm_password'] ?? null;

        // Récupérer l'ID utilisateur depuis la session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté'
            ];
        }

        $userId = $_SESSION['user'];

        try {
            $updateResult = $this->updateUserField($userId, $field, $newValue, $confirmPassword);

            if ($updateResult === true) {
                return [
                    'success' => true,
                    'message' => 'Informations mises à jour avec succès.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $updateResult
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les données utilisateur pour le profil
     */
    public function getProfileData() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté',
                'user' => null
            ];
        }

        $userId = $_SESSION['user'];
        $userInfo = $this->getUserInfos($userId);

        if ($userInfo) {
            return [
                'success' => true,
                'user' => [
                    'firstname' => $userInfo['user_firstname'],
                    'lastname' => $userInfo['user_lastname'],
                    'email' => $userInfo['user_email'],
                    'created_at' => $userInfo['created_at']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Impossible de récupérer les informations utilisateur',
                'user' => [
                    'firstname' => 'Utilisateur',
                    'lastname' => '',
                    'email' => '',
                    'created_at' => ''
                ]
            ];
        }
    }

    /**
     * Vérifier l'authentification pour les pages protégées
     */
    public function requireAuth($redirectTo = 'login') {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            header("Location: $redirectTo");
            exit;
        }
        
        return $_SESSION['user'];
    }

    /**
     * Traiter la page de réinitialisation de mot de passe
     */
    public function handleResetPasswordPage($getParams, $postData = null) {
        $result = [
            'token_valid' => false,
            'success_message' => '',
            'error_message' => '',
            'show_form' => true
        ];

        // Vérifier si un token est fourni
        if (!isset($getParams['token'])) {
            $result['error_message'] = "Aucun token n'a été fourni. Veuillez utiliser le lien envoyé par email.";
            $result['show_form'] = false;
            return $result;
        }

        $token = $getParams['token'];

        try {
            // Vérifier le token
            $tokenResult = $this->verifyResetToken($token);
            
            if ($tokenResult === false) {
                $result['error_message'] = "Le token est invalide ou expiré. Veuillez demander un nouveau lien de réinitialisation.";
                $result['show_form'] = false;
                return $result;
            }

            $result['token_valid'] = true;

            // Traitement du formulaire POST si présent
            if ($postData && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $resetResult = $this->handlePasswordReset(array_merge($postData, ['token' => $token]));
                
                if ($resetResult['success']) {
                    $result['success_message'] = $resetResult['message'];
                    $result['show_form'] = false;
                } else {
                    $result['error_message'] = implode('. ', $resetResult['errors']);
                }
            }

        } catch (Exception $e) {
            $result['error_message'] = "Une erreur s'est produite. Veuillez réessayer ultérieurement.";
            $result['show_form'] = false;
        }

        return $result;
    }
}
?>