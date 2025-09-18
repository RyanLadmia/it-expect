<?php

class HomeController
{
    private $userController;

    public function __construct()
    {
        $this->userController = new UserController();
    }

    /**
     * Gérer la déconnexion globale depuis le layout
     * Cette méthode doit être appelée avant chaque rendu de page
     */
    private function handleGlobalLogout()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
            $result = $this->userController->handleLogout();
            if ($result['success'] && isset($result['redirect'])) {
                header('Location: ' . $result['redirect']);
                exit;
            }
        }
    }

    public function showHome()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        $title = "Cinetech";
        $myView = new View('home');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function show404()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        $title = 'Cinetech - Erreur 404';
        $myView = new View('error404');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showMovie()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        $title = 'Cinetech - Films';
        $myView = new View('movie');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showSerie()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        $title = 'Cinetech - Séries';
        $myView = new View('serie');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showDetail()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        // Utiliser PageController pour la logique complexe de detail
        $pageController = new PageController();
        $data = $pageController->handleDetailPage();
        
        $title = 'Cinetech - Détails';
        $myView = new View('detail');
        $myView->setVars(array_merge(['title' => $title], $data));
        $myView->render();
    }

    public function showProfile()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        // Utiliser PageController pour la logique complexe de profile
        $pageController = new PageController();
        $data = $pageController->handleProfilePage();
        
        $title = 'Cinetech - Profil';
        $myView = new View('profile');
        $myView->setVars(array_merge(['title' => $title], $data));
        $myView->render();
    }

    public function showLogin()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        // Utiliser PageController pour la logique complexe de login
        $pageController = new PageController();
        $data = $pageController->handleLoginPage();
        
        $title = 'Cinetech - Connexion';
        $myView = new View('login');
        $myView->setVars(array_merge(['title' => $title], $data));
        $myView->render();
    }

    public function showVerif()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        $title = 'Cinetech - Vérification';
        $myView = new View('verification');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showFavorite()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        // Utiliser PageController pour la logique complexe de favorite
        $pageController = new PageController();
        $data = $pageController->handleFavoritePage();
        
        $title = 'Cinetech - Mes Favoris';
        $myView = new View('favorite');
        $myView->setVars(array_merge(['title' => $title], $data));
        $myView->render();
    }

    public function showResetPassword()
    {
        // Gérer la déconnexion avant le rendu
        $this->handleGlobalLogout();
        
        // Utiliser PageController pour la logique complexe de reset password
        $pageController = new PageController();
        $data = $pageController->handleResetPasswordPage();
        
        $title = 'Cinetech - Réinitialisation du mot de passe';
        $myView = new View('reset_password');
        $myView->setVars(array_merge(['title' => $title], $data));
        $myView->render();
    }
    
}
?>