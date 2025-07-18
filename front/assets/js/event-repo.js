/*
====================================================================================
    Fichier : event-repo.js

    Rôle :
    Ce fichier gère l’affichage et l’interactivité de la liste globale des événements sur le site.
    Il permet de charger dynamiquement les cartes d’événements, d’activer l’inscription et la désinscription
    à chaque événement pour l’utilisateur connecté, sans recharger la page.

    Fonctionnement :
    - Charge dynamiquement la liste des événements depuis all_events.php via AJAX.
    - Pour chaque événement, attache les gestionnaires d’événement sur les boutons d’inscription et de désinscription.
    - Permet à l’utilisateur de s’inscrire/désinscrire via des requêtes AJAX POST vers les endpoints PHP appropriés.
    - Recharge automatiquement la liste des événements après chaque action pour mettre à jour l’affichage.

    Interactions avec le reste du projet :
    - Utilise all_events.php pour charger la liste des événements.
    - Utilise register_event.php et unregister_event.php pour gérer les inscriptions côté serveur.
    - Doit être associé à une structure HTML contenant l’élément #eventsContainer et des cartes .event-card.

====================================================================================
*/

// Fonction principale pour charger dynamiquement les événements et attacher les actions sur les boutons
async function chargerEvenements() {
  const container = document.getElementById("eventsContainer");
  try {
    // Appel AJAX pour récupérer le HTML des événements
    const response = await fetch("/esportify/back/pages/all_events.php");
    const html = await response.text();
    container.innerHTML = html;

    // Sélectionne toutes les cartes d’événements affichées
    const cartes = container.querySelectorAll(".event-card");

    // Pour chaque carte événement, attache les gestionnaires d’inscription/désinscription
    cartes.forEach((carte) => {
      const id = carte.dataset.eventId;

      const btnIns = carte.querySelector(".register-btn");
      if (btnIns) {
        btnIns.addEventListener("click", () => inscrireAEvent(id));
      }

      const btnDesins = carte.querySelector(".unregister-btn");
      if (btnDesins) {
        btnDesins.addEventListener("click", () => desinscrireAEvent(id));
      }
    });
  } catch (err) {
    // Affiche une erreur et un message si le chargement échoue
    console.error("Erreur lors du chargement des événements :", err);
    container.innerHTML = "<p>Erreur lors du chargement des événements.</p>";
  }
}

// Fonction pour inscrire l’utilisateur connecté à un événement (appel AJAX POST)
async function inscrireAEvent(id_event) {
  try {
    const response = await fetch("/esportify/back/controllers/register_event.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id_event })
    });

    const result = await response.text();
    alert(result);
    chargerEvenements(); // Recharge la liste après action
  } catch (err) {
    alert("Erreur lors de l'inscription.");
    console.error("Erreur fetch inscription :", err);
  }
}

// Fonction pour désinscrire l’utilisateur connecté d’un événement (avec confirmation)
async function desinscrireAEvent(id_event) {
  const confirmation = confirm("Souhaitez-vous vraiment vous désinscrire de cet événement ?");
  if (!confirmation) return;

  try {
    const response = await fetch("/esportify/back/controllers/unregister_event.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id_event })
    });

    const result = await response.text();
    alert(result);
    chargerEvenements(); // Recharge la liste après action
  } catch (err) {
    alert("Erreur lors de la désinscription.");
    console.error("Erreur fetch désinscription :", err);
  }
}

// Initialisation automatique au chargement du DOM : lance le chargement des événements
document.addEventListener("DOMContentLoaded", () => {
  chargerEvenements();
});
