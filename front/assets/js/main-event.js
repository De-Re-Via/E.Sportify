// main-event.js
// Affiche l’événement le plus proche dans la section "PROCHAINEMENT"

async function chargerMainEvent() {
  try {
    const res = await fetch("/esportify/back/pages/next_event.php");
    const event = await res.json();

    // Si aucun événement valide
    if (!event || !event.titre) {
      document.getElementById("mainEventTitle").textContent = "Aucun événement à venir";
      return;
    }

    // Récupération des éléments HTML
    const imgEl = document.getElementById("mainEventImg");
    const titleEl = document.getElementById("mainEventTitle");
    const gameEl = document.getElementById("mainEventGame");
    const dateEl = document.getElementById("mainEventDate");
    const descEl = document.getElementById("mainEventDesc");

    // Injection des données
    if (imgEl) imgEl.src = `assets/events/${event.image_url || "default.jpg"}`;
    if (titleEl) titleEl.textContent = event.titre;
    if (gameEl) gameEl.textContent = event.jeu;
    if (dateEl) dateEl.textContent = `${event.date_event} à ${event.heure_event}`;

    const inscrits = event.inscrits ?? "0";
    const max = event.max_players ?? "N/C";

    if (descEl) {
      descEl.innerHTML = `<strong>Description :</strong> ${event.description}<br>
      <strong>Joueurs :</strong> ${inscrits} / ${max}`;
    }

  } catch (err) {
    console.error("Erreur chargement PROCHAINEMENT :", err);
  }
}

document.addEventListener("DOMContentLoaded", chargerMainEvent);
