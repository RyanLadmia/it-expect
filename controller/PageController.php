<?php

/**
 * Contrôleur central pour toutes les pages
 * Sépare complètement la logique métier de la présentation
 */
class PageController
{
    private $userController;
    private $favoriteController;
    private $commentController;

    public function __construct()
    {
        // Démarrer la session si pas encore démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->userController = new UserController();
        $this->favoriteController = new FavoriteController();
        $this->commentController = new CommentController();
    }


    /**
     * Traiter la page de connexion/inscription
     */
    public function handleLoginPage()
    {
        $data = [
            'errors' => [],
            'successMessage' => null,
            'formType' => 'register'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
            $data['formType'] = $_POST['form_type'] ?? 'register';

            switch ($data['formType']) {
                case 'register':
                    $result = $this->userController->handleRegisterForm($_POST);
                    if ($result['success']) {
                        $data['successMessage'] = $result['message'];
                        if (isset($result['switch_form'])) {
                            $data['formType'] = 'login';
                        }
                    } else {
                        $data['errors'] = $result['errors'];
                    }
                    break;

                case 'login':
                    $result = $this->userController->handleLoginForm($_POST);
                    if ($result['success']) {
                        header('Location: home');
                        exit;
                    } else {
                        $data['errors'] = $result['errors'];
                    }
                    break;

                case 'forgot_password':
                    $result = $this->userController->handlePasswordResetRequest($_POST);
                    if ($result['success']) {
                        $data['successMessage'] = $result['message'];
                    } else {
                        $data['errors'] = $result['errors'];
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * Traiter la page de profil
     */
    public function handleProfilePage()
    {
        // Vérification de l'authentification
        $userId = $this->userController->requireAuth();

        // Traitement AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_request'])) {
            header('Content-Type: application/json');
            $result = $this->userController->handleProfileUpdate($_POST);
            echo json_encode($result);
            exit;
        }

        // Traitement de la déconnexion
        if (isset($_POST['logout'])) {
            $result = $this->userController->handleLogout();
            if ($result['success'] && isset($result['redirect'])) {
                header('Location: ' . $result['redirect']);
                exit;
            }
        }

        // Traitement de la suppression du compte
        if (isset($_POST['delete_account'])) {
            $deleteResult = $this->userController->deleteUserById($userId);
            if ($deleteResult) {
                $this->userController->handleLogout();
                header('Location: home');
                exit;
            }
        }

        // Récupération des données
        $profileData = $this->userController->getProfileData();
        return [
            'user' => $profileData['user'],
            'userId' => $userId
        ];
    }

    /**
     * Traiter la page des favoris
     */
    public function handleFavoritePage()
    {

        // Vérification de l'authentification
        $userId = $this->favoriteController->requireAuthForFavorites();
        $this->favoriteController = new FavoriteController(new ModelFavorite(), $userId);

        // Traitement des requêtes AJAX POST (sauf déconnexion)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
            $this->favoriteController->handleFavoritePageAjax($_POST);
        }

        // Récupération des données
        $pageData = $this->favoriteController->getFavoritesPageData();
        return [
            'favorites' => $pageData['favorites'],
            'success' => $pageData['success'],
            'message' => $pageData['message']
        ];
    }

    /**
     * Traiter la page de détails
     */
    public function handleDetailPage()
    {

        // Validation des paramètres
        $itemId = $_GET['id'] ?? null;
        $itemType = $_GET['type'] ?? null;
        $this->commentController->validateDetailParams($itemId, $itemType);

        // Authentification
        $userId = $this->commentController->requireAuth();
        $isLoggedIn = ($userId !== null);

        // Gestion des requêtes POST (sauf déconnexion)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input) {
                // Ajout aux favoris
                if (!isset($input['content']) && !isset($input['reply_content']) && !isset($input['action'])) {
                    $favoriteController = new FavoriteController(new ModelFavorite(), $userId);
                    $result = $favoriteController->addFavorite(intval($input['id']), $input['type']);
                    
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    exit;
                } else {
                    // Commentaires
                    $this->commentController->handleDetailPageAjax($input, $userId);
                }
            } else {
                // Formulaires classiques
                $this->commentController->handleDetailPagePost($_POST, $userId);
            }
        }

        // Récupération des données
        return [
            'itemId' => $itemId,
            'itemType' => $itemType,
            'userId' => $userId,
            'isLoggedIn' => $isLoggedIn,
            'comments' => $this->commentController->getComments($itemId, $itemType)
        ];
    }

    /**
     * Traiter la page de réinitialisation de mot de passe
     */
    public function handleResetPasswordPage()
    {

        $pageData = $this->userController->handleResetPasswordPage($_GET, $_POST ?? null);
        return [
            'successMessage' => $pageData['success_message'],
            'msgError' => $pageData['error_message'],
            'showForm' => $pageData['show_form']
        ];
    }
}

?>
