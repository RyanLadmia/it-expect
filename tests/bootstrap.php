<?php

// Chargement de l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Configuration des variables d'environnement pour les tests
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/..';

// Chargement de l'autoloader personnalisé du projet
require_once __DIR__ . '/../config/config.php';

// Démarrage de l'autoloader personnalisé
myAutoload::start();

// Configuration pour les tests
ini_set('display_errors', 1);
error_reporting(E_ALL);
