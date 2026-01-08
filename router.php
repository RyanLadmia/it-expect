<?php
// Router pour le serveur PHP intégré (utilisé en CI/CD)
// Ce fichier remplace .htaccess pour le serveur de développement

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$queryString = parse_url($requestUri, PHP_URL_QUERY);

// Si c'est un fichier existant (CSS, JS, images, etc.), le servir directement
if ($requestPath !== '/' && $requestPath !== '') {
    $filePath = __DIR__ . $requestPath;
    // Si c'est un fichier physique existant, le servir
    if (file_exists($filePath) && !is_dir($filePath) && is_file($filePath)) {
        return false; // Servir le fichier tel quel
    }
}

// Gérer les paramètres GET existants
if ($queryString) {
    parse_str($queryString, $queryParams);
    $_GET = array_merge($_GET, $queryParams);
}

// Extraire la route depuis l'URL
$route = 'home'; // Par défaut

if ($requestPath !== '/' && $requestPath !== '') {
    // Enlever le slash initial
    $route = ltrim($requestPath, '/');
    
    // Si l'URL contient /it-expect/, l'enlever
    $route = str_replace('it-expect/', '', $route);
    $route = str_replace('it-expect', '', $route);
    $route = trim($route, '/');
    
    // Si vide après nettoyage, utiliser 'home'
    if (empty($route)) {
        $route = 'home';
    }
}

// Si la route n'est pas déjà définie dans $_GET, l'ajouter
if (!isset($_GET['r'])) {
    $_GET['r'] = $route;
}

// Inclure index.php
require __DIR__ . '/index.php';
