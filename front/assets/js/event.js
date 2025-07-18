/*
====================================================================================
    Fichier : event.js

    Rôle :
    Ce fichier gère la création, l’affichage et la modération des événements côté client sur le dashboard.
    Il propose l'ouverture/fermeture du modal de création, la soumission du formulaire via AJAX,
    l'affichage des événements personnels de l'utilisateur et la modération (validation/refus) par l'administrateur
    ou l'organisateur.

    Fonctionnement :
    - Ouvre/ferme dynamiquement la fenêtre modale de création d’événement.
    - Prend en charge la soumission AJAX du formulaire pour créer un nouvel événement (sans recharger la page).
    - Charge et affiche dynamiquement la liste des événements de l’utilisateur connecté.
    - Permet la validation ou le refus d’un événement par l’administrateur ou l’organisateur via AJAX.
    - Recharge dynamiquement les zones d’affichage selon l’action (événements, modération).

    Interactions avec le reste du projet :
    - Appelle create_event.php pour la création d’événements.
    - Utilise my_events.php pour afficher les événements personnels.
    - Utilise manage_events.php pour la gestion (validation/refus) des événements.
    - Doit être utilisé sur des pages contenant les éléments HTML avec les bons IDs (eventModal, eventForm, eventsList, moderationZone...).

====================================================================================
*/

// Fonctions pour ouvrir et fermer la fenêtre modale de création d’événement
function openEventModal() {
  document.getElementById("eventModal").style.display = "block";
}
function closeEventModal() {
  document.getElementById("eventModal").style.display = "none";
}

// Gestion du formulaire de création d’événement, envoi via AJAX
document.addEventListener("DOMContentLoaded", () => {
  const eventForm = document.getElementById("eventForm");
  if (eventForm) {
    eventForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(eventForm);
      try {
        const response = await fetch("/esportify/back/controllers/create_event.php", {
          method: "POST",
          body: formData
        });
        const text = await response.text();
        if (text.includes("succès")) {
          alert("Événement proposé !");
          eventForm.reset();
          closeEventModal();
        } else {
          alert(text);
        }
      } catch {
        alert("Erreur lors de la création.");
      }
    });
  }
});

// Chargement dynamique des événements créés par l’utilisateur connecté
async function loadMyEvents() {
  const container = document.getElementById("eventsList");
  if (!container) return;
  try {
    const response = await fetch("/esportify/back/pages/my_events.php");
    const html = await response.text();
    container.innerHTML = html;
  } catch {
    container.innerHTML = "<p>Erreur chargement.</p>";
  }
}

// Validation d’un événement (admin/orga) via AJAX avec confirmation utilisateur
async function validerEvent(id) {
  if (!confirm("Valider cet événement ?")) return;
  const formData = new FormData();
  formData.append("event_id", id);
  formData.append("action", "valide");

  const res = await fetch("/esportify/back/pages/manage_events.php", { method: "POST", body: formData });
  const txt = await res.text();
  if (txt === "ok") {
    alert("Validé !");
    loadModeration();
  } else alert(txt);
}

// Refus d’un événement (admin/orga) via AJAX avec confirmation utilisateur
async function refuserEvent(id) {
  if (!confirm("Refuser cet événement ?")) return;
  const formData = new FormData();
  formData.append("event_id", id);
  formData.append("action", "refuse");

  const res = await fetch("/esportify/back/pages/manage_events.php", { method: "POST", body: formData });
  const txt = await res.text();
  if (txt === "ok") {
    alert("Refusé.");
    loadModeration();
  } else alert(txt);
}

// Chargement dynamique de la zone de modération des événements
async function loadModeration() {
  const container = document.getElementById("moderationZone");
  if (!container) return;
  try {
    const res = await fetch("/esportify/back/pages/manage_events.php");
    const html = await res.text();
    container.innerHTML = html;
  } catch {
    container.innerHTML = "<p>Erreur modération.</p>";
  }
}

// Initialisation automatique au chargement de la page : charge les événements et la modération
document.addEventListener("DOMContentLoaded", () => {
  loadMyEvents();
  loadModeration();
});
