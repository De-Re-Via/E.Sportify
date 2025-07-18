
document.addEventListener("DOMContentLoaded", () => {
  const id = new URLSearchParams(window.location.search).get("event_id");

  if (!id) {
    console.error("Aucun ID d'événement fourni dans l'URL.");
    return;
  }

  // Chargement des informations de l'événement
  fetch(`/esportify/back/controllers/get_event_info.php?event_id=${id}`)
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        console.error("Erreur lors du chargement :", data.message);
        return;
      }

      const event = data.event;

      const titleEl = document.getElementById("eventTitle");
      const imgEl = document.getElementById("eventImg");
      const gameEl = document.getElementById("eventGame");
      const dateEl = document.getElementById("eventDate");
      const descEl = document.getElementById("eventDesc");
      const etatEl = document.getElementById("etatEvent");
      const actionEl = document.getElementById("adminActions");

      if (titleEl) titleEl.textContent = event.titre;
      if (imgEl) imgEl.src = "/esportify/front/assets/events/" + event.image_url;
      if (gameEl) gameEl.textContent = event.jeu;
      if (dateEl) dateEl.textContent = event.date_event + " à " + event.heure_event;
      if (descEl) descEl.textContent = event.description;

      const etatText = {
        "attente": "Événement en attente",
        "en_cours": "Événement en cours",
        "termine": "Événement terminé"
      };
      if (etatEl) etatEl.textContent = etatText[event.etat] || "État inconnu";

      // Affichage du bouton "Terminer l'événement" si autorisé
      if (
        (data.role === "admin" || data.role === "organisateur") &&
        data.est_createur === true &&
        event.etat === "en_cours"
      ) {
        if (actionEl) {
          actionEl.innerHTML = `
            <form action="/esportify/back/controllers/admin_actions.php" method="GET"
                  onsubmit="return confirm('Confirmer la fin de l'événement ?');">
              <input type="hidden" name="event_id" value="${id}">
              <button type="submit" class="btn-delete">Terminer l'événement</button>
            </form>`;
        }
      }
    })
    .catch(error => {
      console.error("Erreur lors du chargement de l'événement :", error);
    });

  // Chargement des participants
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
