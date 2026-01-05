<?php
session_start();

// Journaliser la déconnexion
if (isset($_SESSION['user_id'])) {
    error_log("Déconnexion utilisateur - ID: " . $_SESSION['user_id']);
}

// Détruire complètement la session
session_unset();
session_destroy();
session_start();

// Rediriger vers la page de login
$_SESSION['login_success'] = "Vous avez été déconnecté avec succès.";
header('Location: index.php');
exit;
?>