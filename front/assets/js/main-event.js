/*
====================================================================================
    Fichier : main-event.js

    Rôle :
    Ce fichier gère l’affichage dynamique de l’événement à venir le plus proche dans la section
    "PROCHAINEMENT" de la page d’accueil. Il interroge l’API pour obtenir les informations de
    l’événement, puis injecte les données dans le DOM.

    Fonctionnement :
    - Appelle en AJAX le backend (next_event.php) pour obtenir les informations du prochain événement.
    - Si aucun événement n’est trouvé, affiche un message dédié dans la zone concernée.
    - Met à jour les éléments HTML (image, titre, jeu, date, description, jauge de joueurs) avec les
      informations récupérées.
    - Gère les erreurs réseau ou de parsing par un affichage console.

    Interactions avec le reste du projet :
    - Utilise next_event.php côté backend pour récupérer l’événement à venir.
    - Nécessite la présence d’éléments HTML avec les bons IDs (mainEventImg, mainEventTitle, etc.).
    - Est à placer sur la page d’accueil ou tout emplacement où l’affichage du prochain événement est souhaité.

====================================================================================
*/

// Fonction principale pour charger et afficher l'événement à venir
async function chargerMainEvent() {
  try {
    const res = await fetch("/esportify/back/pages/next_event.php");
    const event = await res.json();

    // Cas où aucun événement valide n'est retourné
    if (!event || !event.titre) {
      document.getElementById("mainEventTitle").textContent = "Aucun événement à venir";
      return;
    }

    // Récupération des éléments d'affichage dans le DOM
    const imgEl = document.getElementById("mainEventImg");
    const titleEl = document.getElementById("mainEventTitle");
    const gameEl = document.getElementById("mainEventGame");
    const dateEl = document.getElementById("mainEventDate");
    const descEl = document.getElementById("mainEventDesc");

    // Injection des données dans les éléments correspondants
    if (imgEl) imgEl.src = `assets/events/${event.image_url || "default.jpg"}`;
    if (titleEl) titleEl.textContent = event.titre;
    if (gameEl) gameEl.textContent = event.jeu;
    if (dateEl) dateEl.textContent = `${event.date_event} à ${event.heure_event}`;

    const inscrits = event.inscrits ?? "0";
    const max = event.max_players ?? "N/C";

    // Affichage de la description et de la jauge joueurs
    if (descEl) {
      descEl.innerHTML = `<strong>Description :</strong> ${event.description}<br>
      <strong>Joueurs :</strong> ${inscrits} / ${max}`;
    }

  } catch (err) {
    // Gestion des erreurs de chargement
    console.error("Erreur chargement PROCHAINEMENT :", err);
  }
}

// Démarre le chargement de l'événement dès que la page est prête
document.addEventListener("DOMContentLoaded", chargerMainEvent);
