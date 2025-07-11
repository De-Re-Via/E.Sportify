// main-events.js
// ➤ Rôle : charge dynamiquement les événements à venir (statut = "valide") pour les injecter dans la section SOON...

// Fonction principale
async function chargerEvenements() {
  try {
    // 🔁 Appel AJAX vers le backend PHP qui renvoie les événements à venir
    const reponse = await fetch("../back/pages/soon_events.php");

    // 🔄 Convertit la réponse en JSON (tableau d'événements)
    const events = await reponse.json();

    // 🧱 Cible la section HTML prévue pour recevoir les cartes
    const conteneur = document.getElementById("eventsPreview");

    // 🧹 Vide l'existant (au cas où)
    conteneur.innerHTML = "";

    // 🔁 Pour chaque événement, on génère une carte visuelle
    events.forEach(event => {
      const div = document.createElement("div");
      div.className = "card";

      div.innerHTML = `
        <img src="assets/events/${event.image_url}" alt="${event.titre}" class="event-cover" />
        <h3>${event.titre}</h3>
        <p><strong>Jeu :</strong> ${event.jeu}</p>
        <p><strong>Date :</strong> ${event.date_event} à ${event.heure_event}</p>
      `;

      conteneur.appendChild(div);
    });

  } catch (erreur) {
    console.error("Erreur chargement events :", erreur);
  }
}

// 🚀 Déclenche l'exécution une fois le DOM chargé
document.addEventListener("DOMContentLoaded", chargerEvenements);
