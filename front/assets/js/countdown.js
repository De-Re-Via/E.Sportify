// countdown.js
// ➤ Rôle : charger le prochain événement et afficher un chrono jusqu'à son début

async function loadCountdown() {
  try {
    // Chemin corrigé si on est dans /front
    const res = await fetch("/esportify/back/pages/next_event.php");
    const event = await res.json();

    const countdownEl = document.getElementById("countdown");
    if (!countdownEl) return;

    // Aucun événement à venir
    if (!event || !event.date_event || !event.heure_event) {
      countdownEl.textContent = "Aucun événement à venir";
      return;
    }

    // Date cible
    const target = new Date(`${event.date_event}T${event.heure_event}`);

    const updateCountdown = () => {
      const now = new Date();
      const diff = target - now;

      if (diff <= 0) {
        countdownEl.textContent = "C'est en cours !";
        clearInterval(interval);
        return;
      }

      const seconds = Math.floor(diff / 1000) % 60;
      const minutes = Math.floor(diff / 60000) % 60;
      const hours   = Math.floor(diff / 3600000) % 24;
      const days    = Math.floor(diff / 86400000) % 30;
      const months  = Math.floor(diff / (86400000 * 30));

      countdownEl.textContent =
        `${months} mois : ${days} j : ${hours} h : ${minutes} min : ${seconds} s`;
    };

    updateCountdown();
    const interval = setInterval(updateCountdown, 1000);

  } catch (err) {
    console.error("Erreur chargement chrono :", err);
    const el = document.getElementById("countdown");
    if (el) el.textContent = "Erreur de chargement";
  }
}

document.addEventListener("DOMContentLoaded", loadCountdown);
