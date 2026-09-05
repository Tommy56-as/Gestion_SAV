const form = document.getElementById("installationForm");
const steps = [...document.querySelectorAll(".install-step")];
const overviewSteps = [...document.querySelectorAll("[data-overview-step]")];
const caption = document.getElementById("stepCaption");
let currentStep = 1;

function showStep(stepNumber) {
  currentStep = stepNumber;
  steps.forEach((step) => {
    const visible = Number(step.dataset.step) === stepNumber;
    step.hidden = !visible;
    step.classList.toggle("is-visible", visible);
  });
  overviewSteps.forEach((step) => {
    step.classList.toggle(
      "is-current",
      Number(step.dataset.overviewStep) === stepNumber
    );
  });
  caption.textContent = `Étape ${stepNumber} sur 3`;
}

function validateStep(stepNumber) {
  const step = steps.find((item) => Number(item.dataset.step) === stepNumber);
  const fields = [...step.querySelectorAll("input[required]")];
  const valid = fields.every((field) => field.reportValidity());
  if (stepNumber === 2) {
    const hasType =
      step.querySelectorAll('input[name="types[]"]:checked').length > 0;
    document.getElementById("typesError").hidden = hasType;
    return valid && hasType;
  }
  return valid;
}

document.querySelectorAll(".next-step").forEach((button) => {
  button.addEventListener("click", () => {
    if (validateStep(currentStep)) showStep(currentStep + 1);
  });
});

document.querySelectorAll(".previous-step").forEach((button) => {
  button.addEventListener("click", () => showStep(currentStep - 1));
});

form.addEventListener("submit", (event) => {
  if (!validateStep(3)) event.preventDefault();
});

const initialStep = Number(document.body.dataset.initialStep || 1);
if (initialStep > 1) showStep(initialStep);
