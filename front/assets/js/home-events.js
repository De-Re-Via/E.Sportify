// home-events.js
// ➤ Affiche tous les événements à venir dans la section "SOON..."

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

        const bouton = event.estInscrit
          ? `<button class="unregister-btn" data-event-id="${event.id}">Se désinscrire</button>`
          : `<button class="register-btn" data-event-id="${event.id}">S’inscrire</button>`;

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

      // Bouton S’inscrire
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

      // Bouton Se désinscrire
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
      console.error("Erreur chargement events:", err);
    });
});
