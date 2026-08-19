<?php
/**
 * Page de connexion - Gestion de l'authentification
 */

// Inclure la gestion de l'authentification
require_once('inc/auth.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/miner.jpg" type="image/jpeg" sizes="16*16">
    <link rel="icon" href="img/logo.jpeg" type="image/jpeg" sizes="32*32">
    <link rel="icon" href="img/logo.jpeg" type="image/jpeg" sizes="48*48">
    <link rel="apple-touch-icon" href="img/logo.jpeg" type="image/jpeg" sizes="152*152">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>G.S.A.V - Connexion</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="margin-bottom: 20px;display: flex;justify-content: center;align-items: center;">
                <img src="img/miner.jpg" alt="Logo Gestion SAV"
                    style="width: 200px; height: 200px; border-radius: 50%;">
            </div>
            <h1 style="font-size: 2.5rem;text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);margin-bottom: 10px;">
                Gestion des Services Apres-Ventes
            </h1>
        </div>
        <div class="forms-container">
            <!-- Connexion Form -->
            <section class="form-section">
                <h2 style="font-size: 2.5rem;text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);margin-bottom: 10px;">
                    Connexion
                </h2>
                <!-- Affichage des messages d'erreur disparait apres 5s, refresh ou bouton dismiss-->
                <?php if(isset($message) && !empty($message)): ?>
                <div class="error-messages" id="error-messages" style="margin-bottom: 20px;">
                    <?php foreach($message as $index => $msg): ?>
                    <p class="error-message-item"
                        style="text-align: center; color: #e74c3c; font-weight: 600; margin-bottom: 5px; background: rgba(231, 76, 60, 0.1); padding: 10px; border-radius: 5px;">
                        <?php echo htmlspecialchars($msg); ?>
                        <span class="dismiss-error close" data-message-index="<?php echo $index; ?>">&times;</span>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Modal de succès HTML5 avec CSS3 -->
                <?php if(isset($connexion_success) && $connexion_success): ?>
                <dialog id="success-modal" open>
                    <div class="success-content"
                        style="padding: 2.5rem 2rem; text-align: center; background: #ffffff; border-radius: 1.5rem;">
                        <!-- Icône de succès -->
                        <div style="margin-bottom: 1.5rem;">
                            <i class="fas fa-check-circle success-icon"></i>
                        </div>

                        <!-- Titre -->
                        <h2 style="font-size: 1.75rem; font-weight: 700; color: #047857; margin-bottom: 0.75rem;">
                            Connexion réussie !
                        </h2>

                        <!-- Message de bienvenue -->
                        <p style="color: #374151; margin-bottom: 1.25rem; font-size: 1rem; line-height: 1.5;">
                            Bienvenue
                            <span style="font-weight: 700; color: #10b981;">
                                <?php echo htmlspecialchars($user_type); ?>
                            </span>
                            <br />
                            <?php echo htmlspecialchars($user_name); ?>👋
                        </p>

                        <!-- Message de redirection -->
                        <p style="color: #9ca3af; font-size: 0.875rem; margin-bottom: 2rem; letter-spacing: 0.5px;">
                            Redirection en cours...
                        </p>

                        <!-- Spinner -->
                        <div style="margin-bottom: 2rem;">
                            <div class="spinner"></div>
                        </div>

                        <!-- Compte à rebours -->
                        <p style="font-size: 0.875rem;">
                            <span class="countdown" id="countdown">3</span>
                            <span style="color: #9ca3af; font-weight: 500; margin-left: 0.5rem;">secondes</span>
                        </p>
                    </div>
                </dialog>
                <?php endif; ?>

                <form id="loginForm" action="" method="POST">
                    <div class="form-group">
                        <label for="login-email"
                            style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="login-email" name="email" placeholder="Entrez votre adresse email"
                                value="" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="login-password" style="display: block; margin-bottom: 8px; font-weight: 500;">Mot de
                            passe</label>
                        <div class="input-with-icon password-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="login-password" name="password"
                                placeholder="Entrez votre mot de passe" minlength="8" required>
                            <button type="button" class="password-toggle" id="login-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="button">Se connecter</button>
                    <div class="link-container">
                        <p><a href="#" class="link">Mot de passe oublié ?</a></p>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <!-- Scripts -->
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript" src="js/login.js"></script>

</body>

</html>