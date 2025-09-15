<?php

class HomeController
{

    public function showHome()
    {
        $title = "Cinetech";
        $myView = new View('home');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function show404()
    {
        $title = 'Cinetech - Erreur 404';
        $myView = new View('error404');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showMovie()
    {
        $title = 'Cinetech - Films';
        $myView = new View('movie');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showSerie()
    {
        $title = 'Cinetech - Séries';
        $myView = new View('serie');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showDetail()
    {
        $title = 'Cinetech - Détails';
        $myView = new View('detail');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showProfile()
    {
        $title = 'Cinetech - Profil';
        $myView = new View('profile');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showLogin()
    {
        $title = 'Cinetech - Connexion';
        $myView = new View('login');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showVerif()
    {
        $title = 'Cinetech - Vérification';
        $myView = new View('verification');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showFavorite()
    {
        $title = 'Cinetech - Mes Favoris';
        $myView = new View('favorite');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }

    public function showResetPassword()
    {
        $title = 'Cinetech - Réinitialisation du mot de passe';
        $myView = new View('reset_password');
        $myView->setVars(['title' => $title]);
        $myView->render();
    }
    
}
?>