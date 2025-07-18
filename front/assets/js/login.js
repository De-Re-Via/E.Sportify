/*
====================================================================================
    Fichier : login.js

    Rôle :
    Ce fichier gère la connexion utilisateur via le formulaire de connexion.
    Il traite l’envoi du formulaire en AJAX, affiche le résultat et redirige vers le dashboard
    en cas de succès.

    Fonctionnement :
    - Attache un écouteur d’événement à la soumission du formulaire de connexion (loginForm).
    - Intercepte la soumission, empêche le rechargement par défaut, et envoie les données en AJAX à login.php.
    - Analyse la réponse du serveur :
        - Si le texte de réponse contient "Connexion réussie", affiche une alerte puis redirige vers le dashboard.
        - Sinon, affiche l’erreur retournée par le backend.
    - Gère les erreurs réseau ou serveur par une alerte générique.

    Interactions avec le reste du projet :
    - Utilise login.php pour l’authentification serveur.
    - Nécessite la présence d’un élément #loginForm sur la page concernée.
    - Redirige automatiquement vers dashboard.html en cas de succès.

====================================================================================
*/

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");

  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(loginForm);

      try {
        const response = await fetch("/esportify/back/controllers/login.php", {
          method: "POST",
          body: formData
        });

        const text = await response.text();

        if (text.includes("Connexion réussie")) {
          alert("Connexion réussie !");
          window.location.href = "/esportify/front/dashboard.html";
        } else {
          alert(text);
        }
      } catch (err) {
        console.error("Erreur serveur :", err);
        alert("Erreur lors de la connexion.");
      }
    });
  }
});
