/*
====================================================================================
    Fichier : home-events.js

    Rôle :
    Ce fichier gère l'affichage dynamique de tous les événements à venir dans la section "SOON..." de la page d'accueil.
    Il permet à l'utilisateur connecté de s'inscrire ou de se désinscrire directement depuis l'aperçu,
    en mettant à jour l'affichage sans rechargement manuel.

    Fonctionnement :
    - Charge dynamiquement la liste des événements à venir via AJAX (all_events.php?mode=soon).
    - Pour chaque événement, construit et insère une carte événement contenant les informations et un bouton d'action.
    - Permet à l'utilisateur de s'inscrire ou se désinscrire en AJAX, avec confirmation si besoin.
    - Rafraîchit automatiquement l'affichage après toute action.
    - Gère les erreurs de chargement via la console.

    Interactions avec le reste du projet :
    - Utilise all_events.php en mode "soon" pour récupérer la liste à venir.
    - Utilise register_event.php et unregister_event.php pour l'inscription/désinscription côté serveur.
    - Doit être utilisé sur une page avec un conteneur #eventsPreview pour l'affichage.

====================================================================================
*/

document.addEventListener("DOMContentLoaded", () => {
  fetch("/esportify/back/pages/all_events.php?mode=soon")
    .then(res => res.json())
    .then(events => {
      const container = document.getElementById("eventsPreview");
      if (!container) return;
      container.innerHTML = "";

      events.forEach(event => {
        const card = document.createElement("div");
        card.className = "card";

        // Affichage du bouton d'inscription ou de désinscription selon l'état d'inscription de l'utilisateur
        const bouton = event.estInscrit
          ? `<button class="unregister-btn" data-event-id="${event.id}">Se désinscrire</button>`
          : `<button class="register-btn" data-event-id="${event.id}">S’inscrire</button>`;

        // Construction dynamique du HTML de la carte événement
        card.innerHTML = `
          <img src="assets/events/${event.image_url}" alt="${event.titre}" />
          <div class="event-info">
            <h3>${event.titre}</h3>
            <p><strong>Jeu :</strong> ${event.jeu}</p>
            <p><strong>Date :</strong> ${event.date_event} à ${event.heure_event}</p>
            <p><strong>Description :</strong> ${event.description}</p>
            <p><strong>Joueurs :</strong> ${event.inscrits} / ${event.max_players}</p>
            ${bouton}
          </div>
        `;

        container.appendChild(card);
      });

      // Gestion des boutons "S’inscrire" (inscription AJAX, rafraîchit l'affichage)
      document.querySelectorAll(".register-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = btn.dataset.eventId;
          fetch("/esportify/back/controllers/register_event.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_event: id })
          }).then(res => res.text()).then(alert).then(() => location.reload());
        });
      });

      // Gestion des boutons "Se désinscrire" (désinscription AJAX avec confirmation)
      document.querySelectorAll(".unregister-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = btn.dataset.eventId;
          if (!confirm("Se désinscrire ?")) return;
          fetch("/esportify/back/controllers/unregister_event.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_event: id })
          }).then(res => res.text()).then(alert).then(() => location.reload());
        });
      });
    })
    .catch(err => {
      // Gestion des erreurs de chargement AJAX
      console.error("Erreur chargement events:", err);
    });
});
