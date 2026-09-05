<?php
require_once __DIR__ . '/../inc/bootstrap.php';
require_once __DIR__ . '/../inc/Database.php';
require_once __DIR__ . '/../inc/history.php';
require_once __DIR__ . '/../inc/authorization.php';
require_once __DIR__ . '/../inc/saas.php';

// Vérifier si un utilisateur est connecté. Les permissions sont vérifiées par action.
if (!is_authenticated() || !isset($_SESSION['user_email'])) {
    // Journaliser la tentative d'accès non autorisé
    error_log("Tentative d'accès non autorisé à l'espace admin - IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Rediriger vers la page de login avec un message d'erreur
    $_SESSION['login_errors'] = ["Veuillez vous connecter pour accéder à cette page."];
    header('Location: index.php');
    exit;
}

// Vérifier le timeout de session (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    log_history($pdo, 'Déconnexion automatique après expiration de session', $_SESSION['user_nom'] ?? 'Utilisateur');
    session_unset();
    session_destroy();
    $_SESSION['login_errors'] = ["Session expirée. Veuillez vous reconnecter."];
    header('Location: index.php');
    exit;
}

// Mettre à jour le timestamp d'activité
$_SESSION['last_activity'] = time();
register_user_session();

// Protection contre le clickjacking
header('X-Frame-Options: DENY');

// Protection XSS
header('X-XSS-Protection: 1; mode=block');

// Désactiver la mise en cache pour les pages admin
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>