<?php
session_start();

// Inclure le fichier de connexion à la base de données
require_once('inc/Database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
   
    // Validation des entrées
    if(empty($email) || empty($password)) {
        $message[] = 'Veuillez remplir tous les champs!';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Format d\'email invalide!';
    } else {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        try {
            // Vérifier si l'email existe
            $stmt = $pdo->prepare("SELECT * FROM `utilisateur` WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if($user) {
                // 1. Vérifier le statut du compte (0 = actif, 1 = bloqué)
                if($user['Statut'] == 1) {
                    $message[] = 'Votre compte est bloqué. Contactez l\'administrateur.';
                } 
                // 2. Vérifier le mot de passe
                elseif(!password_verify($password, $user['MotDePasse'])) {
                    $message[] = 'Email ou mot de passe incorrect!';
                    error_log("Tentative de connexion échouée pour: " . $email);
                } 
                // 3. Vérifier le type de compte et authentifier
                else {
                    $typeCompte = $user['TypeDeCompte'] ?? null;
                    
                    // Vérifier que le type de compte existe
                    if(!in_array($typeCompte, ['Administrateur', 'Technicien', 'Caissier'])) {
                        $message[] = 'Type de compte invalide. Contactez l\'administrateur.';
                    } else {
                        // Régénérer l'ID de session pour prévenir les attaques de fixation
                        session_regenerate_id(true);
                        
                        // Stocker les informations utilisateur
                        $_SESSION['user_id'] = $user['idUser'];
                        $_SESSION['user_email'] = $user['Email'];
                        $_SESSION['user_nom'] = $user['Nom_Utilisateur'] ?? 'Utilisateur';
                        $_SESSION['user_type'] = $typeCompte;
                        $_SESSION['user_statut'] = $user['Statut'];
                        $_SESSION['login_time'] = time();
                        
                        // Variable pour indiquer le succès de la connexion
                        $connexion_success = true;
                        $user_type = $typeCompte;
                        $user_name = $user['Nom_Utilisateur'] ?? 'Utilisateur';
                    }
                }
            } else {
                $message[] = 'Cet email n\'existe pas dans notre système!';
            }
            
        } catch(PDOException $e) {
            error_log("Erreur authentification: " . $e->getMessage());
            $message[] = 'Erreur système. Veuillez réessayer.';
        }
    }
} 
  
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logo.jpeg" type="image/jpeg" sizes="16*16">
    <link rel="icon" href="img/logo.jpeg" type="image/jpeg" sizes="32*32">
    <link rel="icon" href="img/logo.jpeg" type="image/jpeg" sizes="48*48">
    <link rel="apple-touch-icon" href="img/logo.jpeg" type="image/jpeg" sizes="152*152">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>G.S.A.V - Connexion</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <canvas id="canvas"></canvas>
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
        <style>
        /* canvas en fond absolu couvrant tout*/
        #canvas {
            position: fixed !important; /*important : fixe au viewport*/
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        </style>

        <div class="forms-container">
            <!-- Connexion Form -->
            <section class="form-section">
                <h2 style="font-size: 2.5rem;text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);margin-bottom: 10px;">
                    Connexion
                </h2>
                <!-- Messages PHP -->
                <?php if(isset($message)): ?>
                <?php foreach($message as $msg): ?>
                <div
                    class="fixed top-4 left-1/2 transform -translate-x-1/2 w-full max-w-md bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between shadow-lg z-50">
                    <span><?php echo $msg; ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- Modal de succès (affiché après validation) -->
                <?php if(isset($connexion_success) && $connexion_success): ?>
                <div id="success-modal"
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div class="bg-white rounded-2xl p-8 text-center shadow-2xl animate-pulse">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-6xl text-green-500"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Connexion réussie !</h2>
                        <p class="text-gray-600 mb-4">Bienvenue <?php echo htmlspecialchars($user_type ) ?>
                            <?php echo htmlspecialchars($user_name)?>👋</p>
                        <p class="text-gray-500 text-sm">Redirection en cours...</p>
                        <div class="mt-4 flex justify-center">
                            <div
                                class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin">
                            </div>
                        </div>
                        <p class="text-gray-400 text-xs mt-4"><span id="countdown">3</span>s</p>
                    </div>
                </div>
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
<script>
    // Gestion du toggle (affichage/masquage) du mot de passe pour les champs de type password
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Récupère l'input password dans le même conteneur parent
            const passwordInput = this.parentElement.querySelector('input');
            // Récupère l'icône FontAwesome dans le bouton
            const icon = this.querySelector('i');

            // Si le type est 'password', le change en 'text' pour afficher le mot de passe
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Change l'icône en œil barré (fa-eye-slash)
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                // Sinon, remet en 'password' pour masquer
                passwordInput.type = 'password';
                // Remet l'icône en œil ouvert (fa-eye)
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Redirection après connexion réussie (si la variable PHP $connexion_success est vraie)
    <?php if(isset($connexion_success) && $connexion_success): ?>
    let countdown = 3; // Compteur initialisé à 3 secondes
    const countdownElement = document.getElementById('countdown'); // Élément HTML pour afficher le compteur

    // Intervalle qui décrémente le compteur toutes les secondes
    const interval = setInterval(() => {
        countdown--; // Décrémente le compteur
        countdownElement.textContent = countdown; // Met à jour l'affichage

        // Quand le compteur atteint 0, arrête l'intervalle et redirige vers home.php
        if (countdown <= 0) {
            clearInterval(interval); // Arrête l'intervalle
            window.location.href = 'home.php'; // Redirection
        }
    }, 1000); // Toutes les 1000 ms (1 seconde)
    <?php endif; ?>

    // Suppression automatique des messages flash (erreurs) après 4 secondes
    setTimeout(() => {
        // Sélectionne tous les éléments avec une classe contenant 'bg-red' (messages d'erreur)
        const flashMessages = document.querySelectorAll('[class*="bg-red"]');
        flashMessages.forEach(msg => {
            // Ajoute une classe CSS pour une animation de disparition (fade-out)
            msg.classList.add('fade-out');
        });
    }, 4000); // Après 4000 ms (4 secondes)

    // Gestion du canvas pour l'animation de fond (particules et étoiles filantes)
    const canvas = document.getElementById('canvas'); // Récupère l'élément canvas
    const ctx = canvas.getContext('2d'); // Contexte 2D pour dessiner

    // Fonction pour redimensionner le canvas à la taille de la fenêtre
    function resizeCanvas() {
        canvas.width = window.innerWidth; // Largeur de la fenêtre
        canvas.height = window.innerHeight; // Hauteur de la fenêtre
    }
    resizeCanvas(); // Appel initial
    // Écouteur pour redimensionner le canvas lors du changement de taille de fenêtre
    window.addEventListener('resize', resizeCanvas);

    // Définition des couleurs pour les particules et étoiles
    const colors = ['#ff00ff', '#00ffff', '#ffff00', '#ff6600', '#ffffff', '#cc00ff'];
    const particles = []; // Tableau pour stocker les particules
    const numParticles = 300; // Réduit à 300 pour de meilleures performances (ajuste si besoin)

    // Variables pour suivre la position de la souris
    let mouseX = -1000; // Position X de la souris (initialisée hors écran pour éviter des calculs initiaux)
    let mouseY = -1000; // Position Y de la souris

    // Écouteur pour mettre à jour la position de la souris lors de son mouvement
    canvas.addEventListener('mousemove', (e) => {
        // Calcule la position relative de la souris dans le canvas
        const rect = canvas.getBoundingClientRect();
        mouseX = e.clientX - rect.left; // Position X relative au canvas
        mouseY = e.clientY - rect.top; // Position Y relative au canvas
        // Log pour déboguer (supprime après test)
        console.log('Souris détectée :', mouseX, mouseY);
    });

    // Boucle pour créer les particules initiales
    for (let i = 0; i < numParticles; i++) {
        particles.push({
            x: Math.random() * canvas.width, // Position X aléatoire
            y: Math.random() * canvas.height, // Position Y aléatoire
            size: Math.random() * 3 + 0.5, // Taille aléatoire (0.5 à 3.5)
            color: colors[Math.floor(Math.random() * colors.length)], // Couleur aléatoire
            speedX: Math.random() * 0.5 - 0.25, // Vitesse X aléatoire (-0.25 à 0.25)
            speedY: Math.random() * 0.5 - 0.25, // Vitesse Y aléatoire (-0.25 à 0.25)
            brightness: Math.random() * 0.7 + 0.3, // Luminosité initiale (0.3 à 1)
            twinkleSpeed: Math.random() * 0.83 + 0.01 // Vitesse de scintillement
        });
    }
    const shootingStars = []; // Tableau pour les étoiles filantes

    // Fonction d'animation principale (appelée en boucle via requestAnimationFrame)
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height); // Efface le canvas à chaque frame

        // Dessine et met à jour chaque particule
        particles.forEach(p => {
            // Calcule la distance entre la particule et la souris
            const dx = mouseX - p.x; // Différence en X
            const dy = mouseY - p.y; // Différence en Y
            const distance = Math.sqrt(dx * dx + dy * dy); // Distance euclidienne

            // Si la souris est proche (rayon de 150 pixels, augmenté pour plus de sensibilité)
            if (distance < 150 && distance > 0) { // Ajout de distance > 0 pour éviter division par zéro
                // Calcule la force d'attraction (plus proche = plus forte)
                const force = (150 - distance) / 150; // Force normalisée (0 à 1)
                const angle = Math.atan2(dy, dx); // Angle entre la particule et la souris

                // Ajoute une accélération vers la souris
                p.speedX += Math.cos(angle) * force * 0.2; // Accélération en X (augmentée à 0.2 pour plus d'effet)
                p.speedY += Math.sin(angle) * force * 0.2; // Accélération en Y

                // Limite la vitesse pour éviter des mouvements trop rapides
                const maxSpeed = 3; // Augmenté à 3 pour plus de dynamisme
                const currentSpeed = Math.sqrt(p.speedX * p.speedX + p.speedY * p.speedY);
                if (currentSpeed > maxSpeed) {
                    p.speedX = (p.speedX / currentSpeed) * maxSpeed;
                    p.speedY = (p.speedY / currentSpeed) * maxSpeed;
                }
            } else {
                // Si la souris est loin, applique une légère friction pour ralentir
                p.speedX *= 0.98; // Réduction de 2% (plus forte pour revenir à la normale)
                p.speedY *= 0.98;
            }

            p.x += p.speedX; // Met à jour la position X
            p.y += p.speedY; // Met à jour la position Y

            // Rebonds sur les bords : inverse la vitesse si hors limites
            if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
            if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;

            // Met à jour la luminosité pour l'effet de scintillement
            p.brightness += p.twinkleSpeed;
            if (p.brightness > 1 || p.brightness < 0.3) p.twinkleSpeed = -p.twinkleSpeed; // Inverse la direction

            ctx.beginPath(); // Commence un nouveau chemin
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2); // Dessine un cercle
            ctx.fillStyle = p.color; // Couleur de remplissage
            ctx.globalAlpha = p.brightness; // Transparence basée sur la luminosité
            ctx.shadowBlur = 20; // Flou de l'ombre
            ctx.shadowColor = p.color; // Couleur de l'ombre
            ctx.fill(); // Remplit le cercle
        });
        ctx.globalAlpha = 1; // Remet la transparence à 100%

         // Dessine et met à jour les étoiles filantes
        shootingStars.forEach((s, index) => {
            s.x += s.speedX; // Met à jour la position X
            s.y += s.speedY; // Met à jour la position Y
            s.life--; // Décrémente la durée de vie

            ctx.beginPath(); // Commence un nouveau chemin
            ctx.moveTo(s.x, s.y); // Point de départ de la ligne
            ctx.lineTo(s.x - s.speedX * 30, s.y - s.speedY * 30); // Point d'arrivée (traînée)
            ctx.strokeStyle = s.color; // Couleur de la ligne
            ctx.lineWidth = 3; // Épaisseur de la ligne
            ctx.shadowBlur = 20; // Flou de l'ombre
            ctx.shadowColor = s.color; // Couleur de l'ombre
            ctx.globalAlpha = s.life / s.maxLife; // Transparence basée sur la vie restante
            ctx.stroke(); // Dessine la ligne

            // Supprime l'étoile si sa vie est écoulée
            if (s.life <= 0) shootingStars.splice(index, 1);
        });
        ctx.globalAlpha = 1; // Remet la transparence à 100%

        requestAnimationFrame(animate); // Rappelle la fonction pour la prochaine frame
    }

        // Intervalle pour créer de nouvelles étoiles filantes toutes les 800 ms
    setInterval(() => {
        // Probabilité de 30% de créer une nouvelle étoile
        if (Math.random() < 0.3) {
            shootingStars.push({
                x: Math.random() * canvas.width * 0.3, // Position X aléatoire (gauche)
                y: Math.random() * canvas.height * 0.4, // Position Y aléatoire (haut)
                speedX: Math.random() * 10 + 8, // Vitesse X (8 à 18)
                speedY: Math.random() * 10 + 8, // Vitesse Y (8 à 18)
                color: colors[Math.floor(Math.random() * colors.length)], // Couleur aléatoire
                life: 50, // Durée de vie initiale
                maxLife: 50 // Durée de vie maximale
            });
        }
    }, 800); // Toutes les 800 ms

    animate(); // Lance l'animation
</script>
</script>
</body>

</html>