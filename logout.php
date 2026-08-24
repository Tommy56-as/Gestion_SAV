<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/Database.php';
require_once __DIR__ . '/inc/history.php';
require_once __DIR__ . '/inc/authorization.php';

// Journaliser la déconnexion
if (isset($_SESSION['user_id'])) {
    $userName = $_SESSION['user_nom'] ?? 'Utilisateur';
    log_history($pdo, 'Déconnexion de l’utilisateur ' . $userName, $userName);
}

remove_user_session();

// Détruire complètement la session
session_unset();
session_destroy();
session_start();

// Rediriger vers la page de login
$_SESSION['login_success'] = "Vous avez été déconnecté avec succès.";
header('Location: index.php');
exit;
?>