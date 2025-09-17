<?php
// ===== SESSION UNIQUEMENT - DÉCONNEXION GÉRÉE DANS CHAQUE PAGE =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo ASSETS;?>css/style.css">
    <link class="favicon" rel="icon" type="image/x-icon" href="<?php echo ASSETS;?>/medias/clapper.png">
    <title><?php echo $title ?? 'Cinetech'; ?></title>
</head>

<body>

    <!-- Link to the top of page -->
    <a id="top"></a>

    <!-- Header -->
    <header>
    <div class="logo">
        <a href="<?php echo BASE_URL;?>">
            <img src="<?php echo ASSETS;?>/medias/logo.png" alt="Logo de Cinetech" />
        </a>
    </div>
    <section class="search-container">
        <input id="search" type="text" name="search" placeholder="Rechercher">
        <section id="suggestion"></section> <!-- Show search suggestions -->
    </section>
    <div class="burger-menu" onclick="toggleMenu()">
        <div class="burger-line"></div>
        <div class="burger-line"></div>
        <div class="burger-line"></div>
    </div>
    <nav class="navbar">
        <ul>
            <li><a href="<?php echo BASE_URL;?>">Accueil</a></li>
            <li><a href="<?php echo BASE_URL;?>movie">Films</a></li>
            <li><a href="<?php echo BASE_URL;?>serie">Séries</a></li>
            <li><a href="<?php echo BASE_URL;?>profile">Profil</a></li>
            <li><a href="<?php echo BASE_URL;?>favorite">Mes Favoris</a></li>
            <?php if (!isset($_SESSION['user'])): ?>
                <li><button class="co"><a href="<?php echo BASE_URL;?>login">Se Connecter</a></button></li>
            <?php else: ?>
                <li>
                    <form method="POST" action="">
                        <button class="deco" type="submit" name="logout">Se déconnecter</button>
                    </form>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>


    <!-- Main -->
    <main>
       <?php echo $contentPage; ?>
    </main>

    <!-- Anchor -->
    <a class="anchor" data-target="#top">↑</a>

    <!-- Footer -->
    <footer>
        <p>&copy; Cinetech Ryan Ladmia 2024</p>
    </footer>

    <!-- Script JS-->
    <script src="<?php echo ASSETS;?>/js/anchor.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/home.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/movie.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/serie.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/detail.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/autocompletion.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/login.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/profile.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/reset_password.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/favorite.js" defer></script>
    <script src="<?php echo ASSETS;?>/js/menu.js" defer></script>
</body>
</html>
