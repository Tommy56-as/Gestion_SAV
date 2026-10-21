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
  const hideErrors = () => {
    errorContainer.hidden = true;
  };
  const dismissButtons = document.querySelectorAll(".dismiss-error");
  dismissButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
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
  const emailInput = document.getElementById("login-email");
  const passwordInput = document.getElementById("login-password");
  const validationMessage = document.getElementById("login-validation-message");

  form.addEventListener("submit", function (e) {
    const email = emailInput.value.trim();
    const password = passwordInput.value;

    if (!email || !password) {
      e.preventDefault();
      showValidationError(
        validationMessage,
        "Veuillez saisir votre adresse e-mail et votre mot de passe."
      );
      return false;
    }

    if (!emailInput.validity.valid) {
      e.preventDefault();
      showValidationError(
        validationMessage,
        "Veuillez saisir une adresse e-mail valide."
      );
      emailInput.focus();
      return false;
    }

    validationMessage.hidden = true;
  });

  emailInput.addEventListener("input", () => {
    if (!emailInput.value || emailInput.validity.valid) {
      validationMessage.hidden = true;
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
function showValidationError(container, message) {
  if (!container) return;
  container.textContent = message;
  container.hidden = false;
}
