<?php
/**
 * Gestion de l'authentification utilisateur
 * Ce fichier traite la connexion et la validation des credentials
 */

session_start();

// Inclure le fichier de connexion à la base de données
require_once(__DIR__ . '/Database.php');
require_once(__DIR__ . '/history.php');

// Initialiser le tableau des messages vide
$message = [];
$connexion_success = false;
$user_type = null;
$user_name = null;

/**
 * Traitement de la requête POST (soumission du formulaire)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation des entrées
    if (empty($email) || empty($password)) {
        $message[] = 'Veuillez remplir tous les champs!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Format d\'email invalide!';
    } else {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        try {
            // Vérifier si l'email existe
            $stmt = $pdo->prepare("SELECT * FROM `utilisateur` WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 1. Vérifier le statut du compte (0 = actif, 1 = bloqué)
                if ($user['Statut'] == 1) {
                    $message[] = 'Votre compte est bloqué. Contactez l\'administrateur.';
                }
                // 2. Vérifier le mot de passe
                elseif (!password_verify($password, $user['MotDePasse'])) {
                    $message[] = 'Email ou mot de passe incorrect!';
                    error_log("Tentative de connexion échouée pour: " . $email);
                }
                // 3. Vérifier le type de compte et authentifier
                else {
                    $typeCompte = $user['TypeDeCompte'] ?? null;
                    
                    // Vérifier que le type de compte existe
                    if (!in_array($typeCompte, ['Administrateur', 'Technicien', 'Caissier'])) {
                        $message[] = 'Type de compte invalide. Contactez l\'administrateur.';
                    } else {
                        // ✅ AUTHENTIFICATION RÉUSSIE
                        // Régénérer l'ID de session pour prévenir les attaques de fixation
                        session_regenerate_id(true);
                        
                        // Stocker les informations utilisateur
                        $_SESSION['user_id'] = $user['idUser'];
                        $_SESSION['user_email'] = $user['Email'];
                        $_SESSION['user_nom'] = $user['Nom_Utilisateur'] ?? 'Utilisateur';
                        $_SESSION['user_type'] = $typeCompte;
                        $_SESSION['user_statut'] = $user['Statut'];
                        $_SESSION['login_time'] = time();
                        $_SESSION['login_success'] = true;

                        log_history($pdo, 'Connexion de l’utilisateur ' . ($_SESSION['user_nom'] ?? 'Utilisateur'));
                        
                        $connexion_success = true;
                        $user_type = $typeCompte;
                        $user_name = $user['Nom_Utilisateur'] ?? 'Utilisateur';
                        
                        // Ne pas rediriger immédiatement pour laisser le modal s'afficher
                        // La redirection sera gérée par JavaScript après 3 secondes
                    }
                }
            } else {
                $message[] = 'Cet email n\'existe pas dans notre système!';
            }
            
        } catch (PDOException $e) {
            error_log("Erreur authentification: " . $e->getMessage());
            $message[] = 'Erreur système. Veuillez réessayer.';
        }
    }
    
    // Stocker les messages d'erreur dans la session s'il y en a
    if (!empty($message)) {
        $_SESSION['error_messages'] = $message;
        // Redirection après POST pour éviter la re-soumission (en cas d'erreur)
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Si succès, ne pas rediriger - laisser le modal s'afficher
    // La redirection vers home.php sera gérée par JavaScript
}

/**
 * Récupération des messages d'erreur de la session (depuis un POST précédent)
 */
if (isset($_SESSION['error_messages'])) {
    $message = $_SESSION['error_messages'];
    unset($_SESSION['error_messages']);
}

/**
 * Vérifier si l'utilisateur est déjà connecté
 * Uniquement rediriger si ce n'est pas un POST qui vient de réussir
 * (Le modal doit s'afficher après le POST avant de rediriger)
 */
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    // Si ce n'est pas un POST (donc une visite directe), rediriger
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        unset($_SESSION['login_success']); // Nettoyer la session
        // Redirection vers home.php
        header('Location: home.php');
        exit();
    } else {
        // Si c'est un POST, garder login_success pour la prochaine visite
        // et afficher le modal
    }
}
