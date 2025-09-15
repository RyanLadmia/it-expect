<?php

class Routeur
{
    private $request;
    private $routes = [

        "home"            => ["controller" => 'HomeController', "method" => 'showHome'],
        "error404"        => ["controller" => 'HomeController', "method" => 'show404'],
        "movie"           => ["controller" => 'HomeController', "method" => 'showMovie'],
        "serie"           => ["controller" => 'HomeController', "method" => 'showSerie'],
        "detail"          => ["controller" => 'HomeController', "method" => 'showDetail'],
        "profile"         => ["controller" => 'HomeController', "method" => 'showProfile'],
        "login"         => ["controller" => 'HomeController', "method" => 'showLogin'],
        "reset_password"         => ["controller" => 'HomeController', "method" => 'showResetPassword'],
        "favorite"        => ["controller" => 'HomeController', "method" => 'showFavorite'],
        "api-config"      => ["controller" => 'ApiController', "method" => 'getApiConfig']

    ];

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function renderController()
    {
        $request = $this->request;

        if (array_key_exists($request, $this->routes)) 
        {
            $controller = $this->routes[$request]['controller'];
            $method     = $this->routes[$request]['method'];

            if (class_exists($controller)) 
            {
                $currentController = new $controller();

                if (method_exists($currentController, $method)) 
                {
                    $currentController->$method();
                } else 
                {
                    $this->redirect404();
                }
            } else 
            {
                $this->redirect404();
            }
        } else 
        {
            $this->redirect404();
        }
    }

    private function redirect404()
    {
        $controller = $this->routes['error404']['controller'];
        $method = $this->routes['error404']['method'];

        $currentController = new $controller();
        $currentController->$method();
    }
    
}
?>