/*
====================================================================================
    Fichier : dashboard.js

    Rôle :
    Ce fichier gère dynamiquement le dashboard utilisateur du site Esportify. Il prend en charge
    le chargement asynchrone des contenus du dashboard, la gestion des actions sur les événements
    (création, démarrage, arrêt, suppression), l’ouverture des modales (événements, points) et
    l’attribution automatique des points aux participants selon leur classement.

    Fonctionnement :
    - Charge dynamiquement le HTML du dashboard depuis le serveur via AJAX et met à jour le DOM.
    - Active les fonctionnalités de gestion d’événements (création, démarrage, arrêt, suppression).
    - Ouvre et gère les modales d’attribution des points et de création d’événement.
    - Prend en charge la soumission de formulaires via AJAX pour éviter le rechargement de la page.
    - Calcule automatiquement les points attribués aux joueurs selon leur classement.
    - Actualise dynamiquement l’affichage après chaque action importante.

    Interactions avec le reste du projet :
    - Travaille avec dashboard.php côté backend pour le contenu HTML.
    - Utilise de nombreux contrôleurs PHP en AJAX : create_event.php, delete_event.php, update_event_status.php, assign_points.php, get_participants.php.
    - Nécessite une structure HTML compatible avec les IDs et classes utilisés (dashboardContent, eventForm, etc.).
    - S’utilise sur la page du dashboard principal des utilisateurs et administrateurs.

====================================================================================
*/

// Variable de blocage pour éviter la double soumission du formulaire de création
let soumissionEnCours = false;

// Au chargement du DOM, chargement dynamique du dashboard et activation des fonctionnalités
document.addEventListener("DOMContentLoaded", async () => {
  const content = document.getElementById("dashboardContent");
  try {
    // Récupère le HTML du dashboard (adapté au rôle utilisateur)
    const response = await fetch("/esportify/back/pages/dashboard.php");
    const html = await response.text();
    content.innerHTML = html;
    activerAttributionPoints(); // Active les boutons d’attribution de points
    activerActionsCartes();     // Active les actions sur les cartes événements
  } catch (err) {
    content.innerHTML = "<p>Erreur chargement dashboard.</p>";
    console.error("Erreur JS :", err);
  }

  // Gestion de la soumission du formulaire de création d'événement
  const eventForm = document.getElementById("eventForm");
  if (eventForm) {
    eventForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (soumissionEnCours) return; // Bloque les soumissions multiples
      soumissionEnCours = true;

      const formData = new FormData(eventForm);
      try {
        const res = await fetch("/esportify/back/controllers/create_event.php", {
          method: "POST",
          body: formData
        });
        const text = await res.text();
        console.log("Réponse création :", text);
        if (res.ok) {
          alert("Événement proposé avec succès.");
          closeEventModal();
          eventForm.reset();
          await actualiserDashboard();
        } else {
          alert("Erreur création : " + text);
        }
      } catch (err) {
        alert("Erreur JS : " + err.message);
      } finally {
        soumissionEnCours = false;
      }
    });
  }
});

// Fonction pour recharger dynamiquement le dashboard (après création, suppression, etc.)
async function actualiserDashboard() {
  try {
    const res = await fetch("/esportify/back/pages/dashboard.php");
    const html = await res.text();
    const container = document.getElementById("dashboardContent");
    container.innerHTML = html;
    activerAttributionPoints();
    activerActionsCartes();
  } catch (err) {
    console.error("Erreur rechargement dashboard :", err);
  }
}

// Activation des boutons "Attribuer les points"
function activerAttributionPoints() {
  document.querySelectorAll("button[onclick^='openPointsModal']").forEach(btn => {
    const eventId = btn.getAttribute("onclick").match(/\d+/)[0];
    btn.onclick = () => openPointsModal(eventId);
  });
}

// Activation des boutons d’action sur les cartes événement (démarrer, terminer, supprimer)
function activerActionsCartes() {
  document.querySelectorAll("button[onclick^='startEvent']").forEach(btn => {
    const id = btn.getAttribute("onclick").match(/\d+/)[0];
    btn.onclick = () => startEvent(id);
  });
  document.querySelectorAll("button[onclick^='endEvent']").forEach(btn => {
    const id = btn.getAttribute("onclick").match(/\d+/)[0];
    btn.onclick = () => endEvent(id);
  });
  document.querySelectorAll("button[onclick^='deleteEvent']").forEach(btn => {
    const id = btn.getAttribute("onclick").match(/\d+/)[0];
    btn.onclick = () => deleteEvent(id);
  });
}

// Suppression d’un événement avec confirmation, via AJAX
function deleteEvent(eventId) {
  if (!confirm("Supprimer cet événement ?")) return;

  const formData = new FormData();
  formData.append("id_event", eventId);

  fetch("/esportify/back/controllers/delete_event.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(txt => {
    console.log("Réponse suppression :", txt);
    alert("Événement supprimé.");
    location.reload();
  })
  .catch(err => {
    console.error("Erreur suppression :", err);
    alert("Erreur lors de la suppression.");
  });
}

// Démarrage d’un événement : change le statut via AJAX puis recharge la page
function startEvent(eventId) {
  fetch('/esportify/back/controllers/update_event_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&new_state=en_cours`
  })
  .then(response => response.text())
  .then(result => {
    console.log("Événement démarré :", result);
    location.reload();
  });
}

// Clôture d’un événement : change le statut via AJAX puis recharge la page
function endEvent(eventId) {
  fetch('/esportify/back/controllers/update_event_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event_id=${eventId}&new_state=termine`
  })
  .then(response => response.text())
  .then(result => {
    console.log("Événement terminé :", result);
    location.reload();
  });
}

// Ouvre la modale d’attribution des points à la fin d’un événement
function openPointsModal(id_event) {
  document.getElementById("pointsEventId").value = id_event;

  fetch("/esportify/back/controllers/get_participants.php?id_event=" + id_event)
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("playersList");
      container.innerHTML = "";
      if (!data || data.length === 0) {
        container.innerHTML = "<p>Aucun participant.</p>";
      } else {
        data.forEach(player => {
          container.innerHTML += `
            <div>
              <strong>${player.username}</strong><br>
              <input type="hidden" name="id[]" value="${player.id_user}" />
              Classement :
              <input type="number" name="classement_${player.id_user}" min="1"
                onchange="calculerPoints(this, ${player.id_user})">
              Points :
              <input type="number" name="points_${player.id_user}" id="points_${player.id_user}" value="0" readonly>
            </div><hr>
          `;
        });
      }
      document.getElementById("pointsModal").style.display = "flex";
    });
}

// Ferme la modale d’attribution des points
function closePointsModal() {
  document.getElementById("pointsModal").style.display = "none";
}

// Calcul automatique des points selon la place attribuée par l’orga
function calculerPoints(input, userId) {
  const pointsInput = document.getElementById("points_" + userId);
  const classement = parseInt(input.value);
  let points = 0;
  if (classement === 1) points = 10;
  else if (classement === 2) points = 8;
  else if (classement === 3) points = 6;
  else if (classement === 4) points = 4;
  else if (classement >= 5) points = 2;
  pointsInput.value = points;
}

// Ouverture et fermeture de la modale de création d’événement
function openEventModal() {
  document.getElementById("eventModal").style.display = "flex";
}
function closeEventModal() {
  document.getElementById("eventModal").style.display = "none";
}
window.openEventModal = openEventModal;
window.closeEventModal = closeEventModal;

// Gestion de la soumission du formulaire d’attribution des points à la fin d’un événement
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("assignPointsForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      try {
        const res = await fetch("/esportify/back/controllers/assign_points.php", {
          method: "POST",
          body: formData
        });

        const text = await res.text();
        alert(text);
        closePointsModal();
      } catch (err) {
        alert("Erreur lors de l'envoi des scores.");
        console.error(err);
      }
    });
  }
});
