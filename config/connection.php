<?php

use Dotenv\Dotenv;

class Connection
{
    public function connectionDB()
    {
        // Charger les variables d'environnement
        require_once 'vendor/autoload.php';
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $db_host = $_ENV['DB_HOST'];
        $db_name = $_ENV['DB_NAME'];
        $db_user = $_ENV['DB_USER'];
        $db_pass = $_ENV['DB_PASS'];
        $db_port = $_ENV['DB_PORT'] ?? '3306'; // Utilise 3306 par défaut si DB_PORT n'est pas défini

        try {
            // Ajout des options PDO pour gérer les tentatives de connexion
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5, // timeout en secondes
                PDO::ATTR_PERSISTENT => false // connexions non persistantes
            ];
            
            // Utilisation du port spécifié dans .env
            $bdd = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8;port=$db_port", $db_user, $db_pass, $options);
            return $bdd;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
