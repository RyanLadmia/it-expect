<?php

class myAutoload
{
    public static function start ()
    {
        spl_autoload_register([__CLASS__, 'autoload']);

        $root = $_SERVER['DOCUMENT_ROOT'];
        $host = $_SERVER['HTTP_HOST'];
        
        // Détecter si on utilise le serveur PHP intégré (CI/CD ou développement local)
        // Le serveur PHP intégré définit DOCUMENT_ROOT au répertoire courant
        $projectDir = dirname(__DIR__); // Répertoire du projet (parent de config/)
        
        // Normaliser les chemins pour comparaison
        $normalizedRoot = realpath($root) ?: $root;
        $normalizedProject = realpath($projectDir) ?: $projectDir;
        
        // Détecter le serveur intégré : DOCUMENT_ROOT correspond au répertoire du projet
        // ou si DOCUMENT_ROOT ne contient pas 'it-expect' mais le projet si
        $isBuiltInServer = (
            $normalizedRoot === $normalizedProject ||
            (strpos($normalizedRoot, '/it-expect') === false && strpos($normalizedProject, '/it-expect') !== false) ||
            $root === '.' ||
            $root === $projectDir
        );
        
        if ($isBuiltInServer) {
            // Utiliser le répertoire du projet comme ROOT
            define('ROOT', $projectDir . '/');
            define('HOST', 'http://' . $host . '/');
            define('BASE_URL', '/');
        } else {
            // Configuration normale (Apache/MAMP)
            define('HOST', 'http://' . $host . '/it-expect/');
            define('ROOT', $root . '/it-expect/');
            define('BASE_URL', '/it-expect/');
        } 

        define('CONTROLLER', ROOT . 'controller/');
        define('VIEW', ROOT . 'view/');
        define('MODEL', ROOT . 'model/');
        define('CLASSES', ROOT . 'classes/');
        define ('CONFIG', ROOT . 'config/');

        define('ASSETS', HOST . 'assets/');

        // Load environment variables after ROOT is defined
        $dotenv = ROOT . '.env';
        if (file_exists($dotenv)) {
            $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!empty($name)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    public static function autoload($class)
    {
        if(file_exists(MODEL . $class . '.php'))
        {
            include_once MODEL . $class . '.php';
        } 
        
        elseif (file_exists(CLASSES . $class . '.php'))
        {
            include_once CLASSES . $class . '.php';
        } 

        elseif (file_exists(CONTROLLER . $class . '.php'))
        {
            include_once CONTROLLER . $class . '.php';
        }
        elseif (file_exists(CONFIG . $class . '.php'))
        {
            include_once CONFIG . $class . '.php';
        }
        else 
        {
            throw new Exception("Class $class not found.");
        }
    }
}
?>