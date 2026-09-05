/**
 * Gestion du formulaire de connexion
 * - Toggle du mot de passe
 * - Gestion des messages d'erreur
 * - Gestion du modal de succès avec redirection automatique
 * - Validation du formulaire
 */

document.addEventListener("DOMContentLoaded", function () {
  initPasswordToggle();
  initErrorMessages();
  initFormValidation();
  initSuccessModal();
  initTemporaryMessages();
});

function initTemporaryMessages() {
  document.querySelectorAll(".temporary-message").forEach((message) => {
    window.setTimeout(() => {
      message.classList.add("is-hidden");
      window.setTimeout(() => message.remove(), 400);
    }, 6000);
  });
}

/**
 * Initialiser le toggle du mot de passe
 */
function initPasswordToggle() {
  const toggleBtn = document.getElementById("login-toggle");
  if (!toggleBtn) return;

  toggleBtn.addEventListener("click", function () {
    const passwordInput = document.getElementById("login-password");
    const icon = this.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.replace("fa-eye-slash", "fa-eye");
    }
  });
}

/**
 * Initialiser la gestion des messages d'erreur
 */
function initErrorMessages() {
  const errorContainer = document.getElementById("error-messages");
  if (!errorContainer) return;

  // Fonction pour masquer le conteneur d'erreurs
  const hideErrors = () => {
    errorContainer.style.transition =
      "opacity 0.3s ease-out, transform 0.3s ease-out";
    errorContainer.style.opacity = "0";
    errorContainer.style.transform = "translateY(-10px)";

    setTimeout(() => {
      errorContainer.style.display = "none";
    }, 300);
  };

  // Disparition automatique après 5 secondes
  const autoHideTimeout = setTimeout(hideErrors, 5000);

  // Gestion des clics sur les boutons dismiss
  const dismissButtons = document.querySelectorAll(".dismiss-error");
  dismissButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      clearTimeout(autoHideTimeout);
      hideErrors();
    });
  });
}

/**
 * Initialiser la validation du formulaire
 */
function initFormValidation() {
  const form = document.getElementById("loginForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    const email = document.getElementById("login-email").value.trim();
    const password = document.getElementById("login-password").value.trim();

    if (!email || !password) {
      e.preventDefault();
      showValidationError("Veuillez remplir tous les champs");
      return false;
    }
  });
}

/**
 * Initialiser la gestion du modal de succès avec redirection
 */
function initSuccessModal() {
  const successModal = document.getElementById("success-modal");
  if (!successModal) return;

  // Vérifier que le dialog est un élément HTML5
  if (successModal.tagName !== "DIALOG") return;

  // Initialiser le compte à rebours
  let countdown = 3;
  const countdownElement = document.getElementById("countdown");

  // Mettre à jour le compte à rebours chaque seconde
  const countdownInterval = setInterval(() => {
    countdown--;
    if (countdownElement) {
      countdownElement.textContent = countdown;
    }

    // Redirection quand le compte à rebours atteint 0
    if (countdown <= 0) {
      clearInterval(countdownInterval);
      window.location.href = "home.php";
    }
  }, 1000);
}

/**
 * Afficher un message d'erreur de validation
 */
function showValidationError(message) {
  alert(message);
  // Optionnel: Vous pouvez remplacer alert() par une notification personnalisée
}
