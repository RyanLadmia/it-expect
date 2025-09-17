<?php
// ===== TRAITEMENT CENTRALISÉ - AUCUNE LOGIQUE DANS LA VUE =====
$pageController = new PageController();
$pageData = $pageController->handleProfilePage();

// Variables pour la vue (extraction simple)
$user = $pageData['user'];
$userId = $pageData['userId'];

$userFirstName = $user['firstname'] ?? 'Utilisateur';
$userLastName = $user['lastname'] ?? '';
$userEmail = $user['email'] ?? '';
$userCreatedAt = $user['created_at'] ?? '';
?>

<div class="profile-wrapper">
    <h2>Bienvenue, <?php echo htmlspecialchars($userFirstName); ?></h2>
    
    <!-- Section d'informations de profil -->
    <div class="profile-info-section">
        <h1>Mes informations</h1>
        <div class="profile-info-wrapper">
            <div class="profile_infos" data-label="Prénom">
                <span class="label-text">Prénom : </span>
                <span id="user_firstname"><?php echo htmlspecialchars($userFirstName); ?></span>
            </div>
            <div class="profile_infos" data-label="Nom">
                <span class="label-text">Nom : </span>
                <span id="user_lastname"><?php echo htmlspecialchars($userLastName); ?></span>
            </div>
            <div class="profile_infos" data-label="Email">
                <span class="label-text">Email : </span>
                <span id="user_email"><?php echo htmlspecialchars($userEmail); ?></span>
            </div>
            <div class="profile_infos" data-label="Date de création">
                <span class="label-text">Compte créé le : </span>
                <span><?php echo date('d/m/Y', strtotime($userCreatedAt)); ?></span>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="profile-action-buttons">
        <button id="edit_button">Modifier mes informations</button>
        
        <!-- Formulaire de déconnexion -->
        <form method="POST" action="" style="width: 100%; max-width: 180px;">
            <button class="profile_deco" type="submit" name="logout">Se déconnecter</button>
        </form>
    </div>

    <!-- Formulaire de modification -->
    <form id="update_form" style="display: none;" class="update-form">
        <div class="form-group">
            <label for="field">Champ à modifier :</label>
            <select id="field" name="field" required>
                <option value="">Sélectionnez un champ</option>
                <option value="user_firstname">Prénom</option>
                <option value="user_lastname">Nom</option>
                <option value="user_email">Email</option>
                <option value="user_password">Mot de passe</option>
            </select>
        </div>

        <div class="form-group">
            <label for="new_value">Nouvelle valeur :</label>
            <input type="text" id="new_value" name="new_value" required>
        </div>

        <div id="password_fields" style="display: none;">
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
        </div>

        <button type="submit" class="profile_update">Mettre à jour</button>
    </form>

    <!-- Messages de retour -->
    <div id="success_message" class="message" style="display: none;"></div>
    <div id="error_message" class="error" style="display: none;"></div>

    <!-- Bouton pour supprimer le compte et système de confirmation -->
    <button id="delete_account_button">Supprimer mon compte</button>
    
    <div id="confirmation_message">
        <p class="msg-delete-account">Voulez-vous vraiment supprimer votre compte ?</p>
        <button id="confirm_delete">Oui</button>
        <button id="cancel_delete">Non</button>
    </div>
    
    <p id="cancel_message">Suppression du compte annulée.</p>
</div>

<script src="assets/js/profile.js"></script>
