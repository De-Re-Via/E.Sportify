// home-events.js
// ➤ Injecte dans la section SOON les prochains events a venir, en lien direct avec soon_events.php
// Lorsque le document est chargé, exécute le code ci-dessous
document.addEventListener("DOMContentLoaded", () => {

  // Appelle le script PHP qui renvoie les événements validés (au format JSON)
  fetch("../back/pages/soon_events.php")
    .then((res) => res.json()) // Convertit la réponse en objet JavaScript
    .then((events) => {
      // Sélectionne la div où les cartes d'événements seront injectées
      const container = document.getElementById("eventsPreview");
      if (!container) return; // Si la div n'existe pas, on sort

      // Vide le contenu au cas où
      container.innerHTML = "";

      // Parcourt chaque événement reçu depuis la base
      events.forEach(event => {
        // Crée un élément <div> avec la classe .card
        const card = document.createElement("div");
        card.classList.add("card");

        // Injecte le contenu HTML de la carte dans le div
        card.innerHTML = `
          <img src="assets/events/${event.image_url}" alt="${event.titre}" class="event-cover" />
          <div class="event-info">
            <h3>${event.titre}</h3>
            <p><strong>Jeu :</strong> ${event.jeu}</p>
            <p><strong>Date :</strong> ${event.date_event}</p>
          </div>
        `;

        // Ajoute cette carte dans le conteneur global
        container.appendChild(card);
      });
    })
    .catch((err) => {
      // En cas d'erreur (ex : problème réseau ou serveur), on logue l’erreur dans la console
      console.error("Erreur chargement events:", err);
    });

});
