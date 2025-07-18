/*
====================================================================================
    Fichier : countdown.js

    Rôle :
    Ce fichier gère l'affichage d'un compte à rebours dynamique vers l'événement à venir le plus proche.
    Il interroge le backend pour obtenir les informations sur l'événement, puis actualise chaque seconde
    l'affichage du décompte sur la page.

    Fonctionnement :
    - Appelle l'API next_event.php pour récupérer les données du prochain événement à venir pour l'utilisateur connecté.
    - Analyse la réponse reçue et initialise le décompte si un événement est trouvé.
    - Met à jour chaque seconde l'affichage du temps restant jusqu'à l'événement.
    - Affiche un message spécifique si aucun événement n'est trouvé ou si une erreur survient.

    Interactions avec le reste du projet :
    - Doit être appelé sur une page contenant un élément avec l'id "countdown".
    - S'appuie sur le backend PHP (next_event.php) pour les informations événementielles.
    - S'utilise généralement sur la page d'accueil, le dashboard, ou dans un widget dédié.

====================================================================================
*/

// Fonction principale asynchrone pour charger et gérer le compte à rebours
async function loadCountdown() {
  try {
    // Appel à l'API pour récupérer le prochain événement
    const res = await fetch("/esportify/back/pages/next_event.php");
    const event = await res.json();

    // Récupération de l'élément d'affichage du compte à rebours
    const countdownEl = document.getElementById("countdown");
    if (!countdownEl) return;

    // Gestion du cas où aucun événement n'est retourné ou informations manquantes
    if (!event || !event.date_event || !event.heure_event) {
      countdownEl.textContent = "Aucun événement à venir";
      return;
    }

    // Construction de la date cible pour le décompte (fusion date et heure de l'événement)
    const target = new Date(`${event.date_event}T${event.heure_event}`);
    // Démarrage d'un intervalle pour actualiser le décompte chaque seconde
    const interval = setInterval(updateCountdown, 1000);

    // Fonction qui met à jour l'affichage du décompte
    function updateCountdown() {
      const now = new Date();
      const diff = target - now;

      // Si la date est atteinte ou dépassée, affiche "C’est en cours !" et arrête le timer
      if (diff <= 0) {
        countdownEl.textContent = "C’est en cours !";
        clearInterval(interval);
        return;
      }

      // Calcul du nombre de jours, heures, minutes et secondes restantes
      const s = Math.floor(diff / 1000) % 60;
      const m = Math.floor(diff / 60000) % 60;
      const h = Math.floor(diff / 3600000) % 24;
      const d = Math.floor(diff / 86400000);
      countdownEl.textContent = `${d} j ${h} h ${m} min ${s} s`;
    }

    // Première mise à jour immédiate du compte à rebours
    updateCountdown();

  } catch (err) {
    // Gestion des erreurs de chargement de l'événement
    const el = document.getElementById("countdown");
    if (el) el.textContent = "Erreur de chargement";
  }
}

// Lancement du compte à rebours une fois la page totalement chargée
document.addEventListener("DOMContentLoaded", loadCountdown);
