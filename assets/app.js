import "./bootstrap.js";
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import "./styles/app.css";

console.log("This log comes from assets/app.js - welcome to AssetMapper! 🎉");

document.addEventListener("turbo:load", function () {
  // Sélectionner tous les boutons de fermeture des toasts
  const closeButtons = document.querySelectorAll('[aria-label="Close"]');

  if (closeButtons) {
    closeButtons.forEach((button) => {
      button.addEventListener("click", function () {
        // Trouver le parent le plus proche qui est le toast
        const toast = this.closest('[role="alert"]');
        if (toast) {
          // Ajouter une classe pour l'animation de fermeture
          toast.classList.add(
            "opacity-0",
            "transition-opacity",
            "duration-300"
          );
          // Supprimer le toast après l'animation
          setTimeout(() => {
            toast.remove();
          }, 300);
        }
      });
    });

    // Optionnel : Fermeture automatique après un certain temps
    setTimeout(() => {
      const toasts = document.querySelectorAll('[role="alert"]');
      toasts.forEach((toast) => {
        toast.classList.add("opacity-0", "transition-opacity", "duration-300");
        setTimeout(() => {
          toast.remove();
        }, 300);
      });
    }, 5000); // Ferme automatiquement après 5 secondes
  }
});
