/*
====================================================================================
    Fichier : next-event.js

    Rôle :
    Ce fichier gère l’affichage dynamique de l’événement à venir le plus proche dans la section principale
    ("mainEvent") de la page d’accueil ou de toute section dédiée. Il récupère les données de l’événement à venir
    via l’API et met à jour les éléments HTML associés.

    Fonctionnement :
    - Appelle en AJAX next_event.php pour obtenir l’événement le plus proche à venir.
    - Si aucun événement n’est trouvé, affiche un message dédié.
    - Met à jour les éléments d’affichage principaux (image, titre, jeu, date, description, jauge d’inscrits).
    - Gère les erreurs réseau ou backend via la console.

    Interactions avec le reste du projet :
    - Utilise next_event.php pour récupérer l’événement à venir.
    - Nécessite la présence des éléments HTML avec les bons IDs (mainEventImg, mainEventTitle, etc.).
    - Utilisé sur la page d’accueil ou toute section mettant en avant le prochain événement.

====================================================================================
*/

// Fonction principale pour charger et afficher le prochain événement
async function chargerProchainEvent() {
  try {
    const response = await fetch("/esportify/back/pages/next_event.php");
    const event = await response.json();

    // Affichage message si aucun événement à venir n’est trouvé
    if (!event || !event.titre) {
      document.getElementById("mainEventTitle").textContent = "Aucun événement à venir";
      return;
    }

    // Mise à jour des éléments d’affichage avec les données de l’événement
    document.getElementById("mainEventImg").src = `assets/events/${event.image_url || "default.jpg"}`;
    document.getElementById("mainEventTitle").textContent = event.titre;
    document.getElementById("mainEventGame").textContent = event.jeu;
    document.getElementById("mainEventDate").textContent = `${event.date_event} à ${event.heure_event}`;
    document.getElementById("mainEventDesc").textContent =
      `${event.description} (${event.inscrits} inscrits / ${event.max_players})`;

  } catch (error) {
    // Gestion des erreurs réseau ou parsing
    console.error("Erreur chargement du prochain event :", error);
  }
}

// Lance le chargement de l’événement dès que la page est chargée
document.addEventListener("DOMContentLoaded", chargerProchainEvent);
