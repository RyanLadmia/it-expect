<?php
include_once 'config/config.php';

myAutoload::start();

// Get the road (param 'r' in URL)
$request = $_GET['r'] ?? 'home';  // home.php?r...

$routeur = new Routeur($request);
$routeur->renderController();
?> 