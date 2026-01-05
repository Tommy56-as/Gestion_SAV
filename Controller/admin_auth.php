<?php
session_start();

// Vérifier si un admin est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])|| !isset($_SESSION['user_email'])) {
    // Journaliser la tentative d'accès non autorisé
    error_log("Tentative d'accès non autorisé à l'espace admin - IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Rediriger vers la page de login avec un message d'erreur
    $_SESSION['login_errors'] = ["Accès réservé aux administrateurs."];
    header('Location: index.php');
    exit;
}

// Vérifier le timeout de session (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    session_unset();
    session_destroy();
    $_SESSION['login_errors'] = ["Session expirée. Veuillez vous reconnecter."];
    header('Location: index.php');
    exit;
}

// Mettre à jour le timestamp d'activité
$_SESSION['last_activity'] = time();

// Protection contre le clickjacking
header('X-Frame-Options: DENY');

// Protection XSS
header('X-XSS-Protection: 1; mode=block');

// Désactiver la mise en cache pour les pages admin
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>