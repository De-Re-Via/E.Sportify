// next-event.js
// ➤ Affiche l'événement le plus proche (mainEvent)

async function chargerProchainEvent() {
  try {
    const response = await fetch("/esportify/back/pages/next_event.php");
    const event = await response.json();

    if (!event || !event.titre) {
      document.getElementById("mainEventTitle").textContent = "Aucun événement à venir";
      return;
    }

    document.getElementById("mainEventImg").src = `assets/events/${event.image_url || "default.jpg"}`;
    document.getElementById("mainEventTitle").textContent = event.titre;
    document.getElementById("mainEventGame").textContent = event.jeu;
    document.getElementById("mainEventDate").textContent = `${event.date_event} à ${event.heure_event}`;
    document.getElementById("mainEventDesc").textContent =
      `${event.description} (${event.inscrits} inscrits / ${event.max_players})`;

  } catch (error) {
    console.error("Erreur chargement du prochain event :", error);
  }
}

document.addEventListener("DOMContentLoaded", chargerProchainEvent);
