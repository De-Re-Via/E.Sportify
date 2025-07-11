// next-event.js
// ➤ Injecte dans la section PROCHAINEMENT les infos du prochain événement à venir, en lien direct avec next-event.php

async function chargerProchainEvent() {
  try {
    const response = await fetch("../back/pages/next_event.php");
    const event = await response.json();

    if (!event || !event.titre) {
      document.getElementById("mainEventTitle").textContent = "Aucun événement à venir";
      return;
    }

    // 🖼️ Chargement des données dans la bannière
    document.getElementById("mainEventImg").src = `assets/events/${event.image_url || "default.jpg"}`;
    document.getElementById("mainEventTitle").textContent = event.titre;
    document.getElementById("mainEventGame").textContent = event.jeu;
    document.getElementById("mainEventDate").textContent = `${event.date_event} à ${event.heure_event}`;
    document.getElementById("mainEventDesc").textContent = event.description;

  } catch (error) {
    console.error("Erreur chargement du prochain event :", error);
  }
}

// Lancement dès que la page est prête
document.addEventListener("DOMContentLoaded", chargerProchainEvent);
