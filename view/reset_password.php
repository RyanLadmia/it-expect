<?php
// Supprimer les instructions de débogage
// error_log("Paramètres GET reçus: " . json_encode($_GET));

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // error_log("Token reçu dans la page: " . $token);

    $user = new ModelUser();
    $successMessage = ''; // Initialisation du message de succès
    $msgError = ''; // Initialisation du message d'erreur

    // Vérifier le token
    try {
        $result = $user->verifyResetToken($token);
        // error_log("Résultat de la vérification du token: " . ($result ? "valide (user_id: $result)" : "invalide"));

        if ($result === false) {
            $msgError = "Le token est invalide ou expiré. Veuillez demander un nouveau lien de réinitialisation.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
                    // Validation du mot de passe
                    $newPassword = $_POST['new_password'];
                    $confirmPassword = $_POST['confirm_password'];
                    
                    if (strlen($newPassword) < 8) {
                        $msgError = "Le mot de passe doit contenir au moins 8 caractères.";
                    } elseif ($newPassword !== $confirmPassword) {
                        $msgError = "Les mots de passe ne correspondent pas.";
                    } else {
                        // Mise à jour du mot de passe
                        $updateResult = $user->updatePassword($token, $newPassword);
                        
                        if ($updateResult) {
                            $successMessage = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                        } else {
                            $msgError = "Une erreur s'est produite lors de la mise à jour du mot de passe.";
                        }
                    }
                } else {
                    $msgError = "Veuillez remplir tous les champs.";
                }
            }
        }
    } catch (Exception $e) {
        // error_log("Exception lors de la réinitialisation: " . $e->getMessage());
        $msgError = "Une erreur s'est produite. Veuillez réessayer ultérieurement.";
    }
} else {
    // Rediriger vers la page de connexion si aucun token n'est fourni
    // error_log("Aucun token n'a été fourni dans l'URL");
    $msgError = "Aucun token n'a été fourni. Veuillez utiliser le lien envoyé par email.";
}
?>

<div class="reset_wrapper">
    <?php if (empty($successMessage)): ?>
        <!-- Formulaire de réinitialisation de mot de passe -->
        <form id="reset-password-form" method="POST" action="">
            <h2>Réinitialisation du mot de passe</h2>
            
            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" name="new_password" id="new_password" required minlength="8" placeholder="8 caractères minimum">
            
            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirmer votre mot de passe">
            
            <input type="submit" value="Réinitialiser mon mot de passe">
        </form>
    <?php endif; ?>

    <!-- Affichage du message d'erreur -->
    <?php if (!empty($msgError)): ?>
    <div class="reset_error">
        <p class="msgError"><?= $msgError ?></p>
    </div>
    <?php endif; ?>

    <!-- Affichage du message de succès -->
    <?php if (!empty($successMessage)): ?>
    <div class="message_confirm">
        <?= $successMessage ?>
        <p><a href="login" class="login-from-success">Se connecter</a></p>
    </div>
    <?php endif; ?>
</div>


