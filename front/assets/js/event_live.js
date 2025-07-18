/*
====================================================================================
    Fichier : event_live.js

    Rôle :
    Ce fichier gère le chargement dynamique des informations d'un événement en cours et de ses participants.
    Il permet d'afficher toutes les données importantes d'un événement sélectionné (titre, image, jeu, date, état, etc.)
    et de mettre à jour dynamiquement la liste des participants sans recharger la page.

    Fonctionnement :
    - Récupère l'identifiant de l'événement depuis l'URL (paramètre event_id).
    - Appelle l'API get_event_info.php pour obtenir les données détaillées de l'événement (titre, image, jeu, date, état...).
    - Affiche dynamiquement ces données dans les éléments HTML de la page prévus à cet effet.
    - Adapte les actions d'administration affichées selon le rôle et le statut de l'utilisateur.
    - Charge la liste des participants depuis get_participants.php et met à jour l'affichage dynamiquement.
    - Gère les erreurs réseau ou de données via la console.

    Interactions avec le reste du projet :
    - S'appuie sur le backend PHP pour toutes les données (get_event_info.php et get_participants.php).
    - Requiert une structure HTML contenant les éléments avec les IDs correspondants (eventTitle, eventImg, eventGame, etc.).
    - Utilisé pour la page événement live (ex : event_live.html).

====================================================================================
*/

// Exécution du script après chargement complet du DOM
document.addEventListener("DOMContentLoaded", () => {
  // Récupération de l'identifiant de l'événement dans l'URL (paramètre event_id)
  const id = new URLSearchParams(window.location.search).get("event_id");

  if (!id) {
    // Affichage d'une erreur en console si aucun identifiant n'est fourni
    console.error("Aucun ID d'événement fourni dans l'URL.");
    return;
  }

  // Chargement des informations détaillées de l'événement
  fetch(`/esportify/back/controllers/get_event_info.php?event_id=${id}`)
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        // Affichage d'une erreur si le backend retourne une erreur
        console.error("Erreur lors du chargement :", data.message);
        return;
      }

      const event = data.event;

      // Récupération des éléments HTML cibles pour l'affichage des données événementielles
      const titleEl = document.getElementById("eventTitle");
      const imgEl = document.getElementById("eventImg");
      const gameEl = document.getElementById("eventGame");
      const dateEl = document.getElementById("eventDate");
      const descEl = document.getElementById("eventDesc");
      const etatEl = document.getElementById("etatEvent");
      const actionEl = document.getElementById("adminActions");

      // Affichage des informations principales de l'événement
      if (titleEl) titleEl.textContent = event.titre;
      if (imgEl) imgEl.src = "/esportify/front/assets/events/" + event.image_url;
      if (gameEl) gameEl.textContent = event.jeu;
      if (dateEl) dateEl.textContent = event.date_event + " à " + event.heure_event;
      if (descEl) descEl.textContent = event.description;

      // Affichage de l'état de l'événement sous forme lisible
      const etatText = {
        "attente": "Événement en attente",
        "en_cours": "Événement en cours",
        "termine": "Événement terminé"
      };
      if (etatEl) etatEl.textContent = etatText[event.etat] || "État inconnu";

      // Affichage du bouton "Terminer l'événement" si l'utilisateur a les droits
      if (
        (data.role === "admin" || data.role === "organisateur") &&
        data.est_createur === true &&
        event.etat === "en_cours"
      ) {
        if (actionEl) {
          actionEl.innerHTML = `
            <form action="/esportify/back/controllers/admin_actions.php" method="GET"
                  onsubmit="return confirm('Confirmer la fin de l\'événement ?');">
              <input type="hidden" name="event_id" value="${id}">
              <button type="submit" class="btn-delete">Terminer l'événement</button>
            </form>`;
        }
      }
    })
    .catch(error => {
      // Affichage d'une erreur en console si le chargement échoue
      console.error("Erreur lors du chargement de l'événement :", error);
    });

  // Chargement dynamique de la liste des participants à l'événement
  fetch(`/esportify/back/controllers/get_participants.php?id_event=${id}`)
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("participantsList");
      container.innerHTML = "<h3>Participants :</h3>";
      if (!data || data.length === 0) {
        container.innerHTML += "<p>Aucun participant pour l'instant.</p>";
      } else {
        data.forEach(user => {
          container.innerHTML += `<p>${user.username}</p>`;
        });
      }
    })
    .catch(err => console.error("Erreur chargement participants :", err));
});
